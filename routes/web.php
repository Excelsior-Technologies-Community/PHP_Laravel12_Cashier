<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Http\Request;

// Welcome page
Route::get('/', fn () => view('welcome'));

// Authentication Routes
Route::get('/login', fn () => view('login'))->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', fn () => view('register'))->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware('auth')
    ->name('dashboard');

// Subscription Routes (with auth middleware)
Route::middleware(['auth'])->group(function () {
    // Plans
    Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
    
    // Subscribe to plan
    Route::get('/subscribe/{plan}', function (Request $request, $plan) {
        $user = $request->user();
        
        $priceId = ($plan === 'pro') ? env('STRIPE_PRICE_PRO') : env('STRIPE_PRICE_BASIC');
        
        if (!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }
        
        $subscription = $user->newSubscription('default', $priceId);
        
        if ($plan === 'pro') {
            $subscription->trialDays(7);
        }
        
        return $subscription
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('dashboard') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('plans'),
            ]);
    })->name('subscribe');
    
    // Manage Subscription
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
    Route::post('/update-payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->name('update.payment.method');
    
    // Invoices
    Route::get('/invoices', [SubscriptionController::class, 'invoices'])->name('invoices');
    Route::get('/download-invoice/{invoice}', function (Request $request, $invoiceId) {
        return $request->user()->downloadInvoice($invoiceId, [
            'vendor' => 'Your Company Name',
            'product' => 'Monthly Subscription',
        ]);
    })->name('download.invoice');
    
    // Billing Portal
    Route::get('/billing-portal', function (Request $request) {
        return $request->user()->redirectToBillingPortal(route('dashboard'));
    })->name('billing');
    
    // Premium Content (requires subscription)
    Route::get('/premium-content', fn () => view('premium'))
        ->middleware('subscribed')
        ->name('premium.content');
});

// Testing Routes (Development only) - Keep outside auth group or add separately
if (app()->environment('local')) {
    Route::middleware(['auth'])->group(function () {
        Route::get('/fake-subscribe', function () {
            $user = auth()->user();
            if (!$user) return redirect('/login');
            
            $old = $user->subscription('default');
            if ($old) {
                $old->delete();
            }
            
            $user->subscriptions()->create([
                'type' => 'default',
                'stripe_id' => 'fake_' . \Illuminate\Support\Str::random(10),
                'stripe_status' => 'active',
                'stripe_price' => 'price_fake',
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => now()->addMonth(),
            ]);
            
            return redirect('/dashboard')->with('success', 'Fake subscription activated for testing!');
        })->name('fake.subscribe');
        
        Route::post('/fake-cancel', function () {
            $sub = auth()->user()->subscription('default');
            if ($sub) {
                $sub->update(['stripe_status' => 'canceled', 'ends_at' => now()]);
            }
            return redirect()->route('dashboard')->with('success', 'Fake subscription cancelled!');
        })->name('fake.cancel');
    });
}

// Stripe Webhook (no auth needed)
Route::post('/stripe/webhook', [App\Http\Controllers\WebhookController::class, 'handleWebhook']);