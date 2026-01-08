<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Auth routes (simple)
Route::get('/login', function () { return view('login'); })->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', function () { return view('register'); })->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/dashboard', function () { return view('dashboard'); })->middleware('auth')->name('dashboard');

// Stripe Subscription Checkout
Route::get('/subscribe', function (Request $request) {
    return $request->user()->newSubscription('default', env('STRIPE_PRICE_MONTHLY'))->checkout([
        'success_url' => route('subscribe.success'),
        'cancel_url' => route('subscribe.cancel'),
    ]);
})->middleware('auth')->name('subscribe');


Route::get('/subscribe/success', function () { return "Subscription Successful!"; })->name('subscribe.success');
Route::get('/subscribe/cancel', function () { return "Subscription Cancelled"; })->name('subscribe.cancel');

// Billing Portal
Route::get('/billing-portal', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('dashboard'));
})->middleware('auth')->name('billing');

// Webhook
Route::post('/stripe/webhook', [App\Http\Controllers\WebhookController::class, 'handleWebhook']);
