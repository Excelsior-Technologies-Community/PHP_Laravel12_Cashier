<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

Route::get('/', fn () => view('welcome'));

// ===================
// AUTH
// ===================
Route::get('/login', fn () => view('login'))->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', fn () => view('register'))->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===================
// DASHBOARD
// ===================
Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware('auth')
    ->name('dashboard');

// ===================
// STRIPE SUBSCRIPTION (Plans + Trial + Coupons)
// ===================
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
            'cancel_url' => route('dashboard'),
        ]);
})->middleware('auth')->name('subscribe');

// ===================
// PREMIUM CONTENT (Middleware)
// ===================
Route::get('/premium-content', function () {
    return "This is premium content for subscribed users only!";
})->middleware(['auth', 'subscribed'])->name('premium.content');

// ===================
// INVOICES (Receipts)
// ===================
Route::get('/download-invoice/{invoice}', function (Request $request, $invoiceId) {
    return $request->user()->downloadInvoice($invoiceId, [
        'vendor' => 'Your Company Name',
        'product' => 'Monthly Subscription',
    ]);
})->middleware('auth')->name('download.invoice');

// ===================
// BILLING PORTAL
// ===================
Route::get('/billing-portal', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('dashboard'));
})->middleware('auth')->name('billing');

// ===================
// STRIPE WEBHOOK
// ===================
Route::post('/stripe/webhook', [App\Http\Controllers\WebhookController::class, 'handleWebhook']);

// ===================
// FAKE SUBSCRIPTION (LOCAL TEST)
// ===================
Route::get('/fake-subscribe', function () {
    $user = auth()->user();
    if (!$user) return redirect('/login');

    $old = $user->subscription('default');
    if ($old) {
        $old->update(['stripe_status' => 'canceled', 'ends_at' => now()]);
    }

    $user->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'fake_' . Str::random(10),
        'stripe_status' => 'active',
        'stripe_price' => 'price_fake',
        'quantity' => 1,
        'trial_ends_at' => null,
        'ends_at' => now()->addMonth(),
    ]);

    return redirect('/dashboard')->with('message', 'Fake subscription activated!');
})->middleware('auth')->name('fake.subscribe');

// ===================
// CANCEL FAKE SUBSCRIPTION
// ===================
Route::post('/fake-cancel', function () {
    $user = Auth::user();
    $sub = $user->subscription('default');
    if ($sub) {
        $sub->update(['stripe_status' => 'canceled', 'ends_at' => now()]);
    }
    return redirect()->route('dashboard')->with('message', 'Subscription cancelled!');
})->middleware('auth')->name('fake.cancel');