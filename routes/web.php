<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

// Home
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
// STRIPE SUBSCRIPTION
// ===================
Route::get('/subscribe', function (Request $request) {

    $user = $request->user();

    if (!$user->stripe_id) {
        $user->createAsStripeCustomer();
    }

    return $user->newSubscription('default', env('STRIPE_PRICE_MONTHLY'))
        ->checkout([
            'success_url' => route('subscribe.success'),
            'cancel_url' => route('subscribe.cancel'),
        ]);

})->middleware('auth')->name('subscribe');

Route::get('/subscribe/success', fn () => "Subscription Successful!")
    ->name('subscribe.success');

Route::get('/subscribe/cancel', fn () => "Subscription Cancelled")
    ->name('subscribe.cancel');


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

    if (!$user) {
        return redirect('/login');
    }

    // ❗ Cancel old subscription properly
    $old = $user->subscription('default');

    if ($old) {
        $old->update([
            'stripe_status' => 'canceled',
            'ends_at' => now(),
        ]);
    }

    // Create fake subscription
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
        $sub->update([
            'stripe_status' => 'canceled',
            'ends_at' => now(),
        ]);
    }

    return redirect()->route('dashboard')
        ->with('message', 'Subscription cancelled!');
})->middleware('auth')->name('fake.cancel');