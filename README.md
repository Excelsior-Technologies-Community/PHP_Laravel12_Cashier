# PHP_Laravel12_Cashier


---

Technology Stack:

 PHP 8+, Laravel 12, MySQL, Stripe Payment Gateway, Laravel Cashier
Purpose: A simple Laravel application demonstrating user authentication, subscription management, and Stripe billing integration using Laravel Cashier.

## Project Overview

This project is a subscription-based web application built using Laravel 12. Users can:

Register and login to the system.

Subscribe to a paid plan using Stripe.

Manage their subscription via the Stripe billing portal.

View subscription status on a dashboard.

Logout securely.

It uses Laravel Cashier, which is Laravel’s official package for integrating Stripe billing, making it easy to manage subscriptions, invoices, and payments.


## Features
- User registration and login
- Dashboard showing subscription status
- Subscribe to Stripe plan
- Billing portal to manage subscription
- Webhook handling for Stripe events
- Logout securely




## Prerequisites
- PHP >= 8.2
- Composer
- MySQL
- Stripe Account (for API keys)
- Node.js & NPM (if using Laravel Mix / front-end assets)


---





# Step-by-Step Laravel Cashier (Stripe) Setup


---

## STEP 1: Create Laravel 12 Project

### Command:

```
composer create-project laravel/laravel PHP_Laravel12_Cashier "12.*"

```

### Go inside project:
```
cd PHP_Laravel12_Cashier

```


## Step 2 : Install Laravel Cashier Package

### Install Cashier using Composer:

```
composer require laravel/cashier

```

This adds the Stripe billing integration for Laravel



## Step 3 : Publish Cashier Migrations

### Publish migrations so Cashier can add billing tables:

```
php artisan vendor:publish --tag="cashier-migrations"

```

This prepares migrations for:

extra Stripe related fields on users table
subscriptions table
subscription_items table



## STEP 4: Configure Database

### Open .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_cashier
DB_USERNAME=root
DB_PASSWORD=

```

Create database in phpMyAdmin:
```
laravel12_cashier

```


### Run Migrations:

```
php artisan migrate

```


Explanation:

Connects Laravel to your MySQL database

Make sure export_api_db exists in phpMyAdmin

This applies the migrations in your database




## Step 5 : (Optional) Publish Cashier Config

### To customize billing behavior:

```
php artisan vendor:publish --tag="cashier-config"

```

This creates config/cashier.php


## Step 6 : Configure Your Billable Model and Migration

### Open Migration: database/migrations/xxxx_create_users_table.php and replace this:

```

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            //  CASHIER COLUMNS (VERY IMPORTANT)
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

```






### Open app/Models/User.php, add the Billable trait:

```
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}

```

This enables subscription & billing functions on your user model.



## Step 7 : Add Your Stripe API Keys

### In your .env file:

```

STRIPE_KEY=pk_test_1234…
STRIPE_SECRET=sk_test_5678…
STRIPE_WEBHOOK_SECRET=whsec_…
STRIPE_PRICE_MONTHLY=price_1Axxxxxxxx


```

Get keys from:

Stripe Dashboard → Developers → API Keys



## Step 8 : (Optional) Enable Tax Calculation

### In AppServiceProvider.php:

```
use Laravel\Cashier\Cashier;

public function boot(): void
{
    Cashier::calculateTaxes();
}

```
This enables automatic Stripe tax calculation




## Step 9 : Stripe Webhooks Setup

### Cashier needs to receive Stripe webhook events to keep billing data up-to-date.

Run:

```
php artisan cashier:webhook

```
This creates a webhook endpoint used by Stripe (default path /stripe/webhook)


### Then, in your Stripe dashboard, set webhook URL to:

```
https://your-app-url/stripe/webhook

```

And add webhook secret into .env:
STRIPE_WEBHOOK_SECRET=...




## STEP 10 : write providers 

### Open Config/services.php and add :

```
 'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],

```

## STEP 11 : Routes

### File: routes/web.php

Defines routes :

```
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


```


## STEP 12 : Create Controller

### Run command:

```

php artisan make:controller AuthController 

php artisan make:controller WebhookController

```

### File created: app/Http/Controllers/AuthController.php

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6|confirmed'
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email','password');

        if(Auth::attempt($credentials)){
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email'=>'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }
}

```


### File created: app/Http/Controllers/WebhookController.php


```

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class WebhookController extends CashierWebhookController
{
    /**
     * Handle a Stripe webhook call.
     *
     * Cashier automatically handles these events:
     * - invoice.payment_succeeded
     * - invoice.payment_failed
     * - customer.subscription.created
     * - customer.subscription.updated
     * - customer.subscription.deleted
     */

    public function handleWebhook(Request $request)
    {
        // Log all webhook events for debugging
        Log::info('Stripe Webhook Received: ', $request->all());

        // Call Cashier to handle subscription events automatically
        return parent::handleWebhook($request);
    }

    /**
     * Optional: Handle a specific event manually
     * Example: when subscription is created
     */
    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::info("Subscription Created for user: " . $user->email);
            // You can do additional logic here if needed
        }

        return response()->json(['received' => true]);
    }

    /**
     * Optional: Handle subscription cancelled
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::info("Subscription Cancelled for user: " . $user->email);
        }

        return response()->json(['received' => true]);
    }
}

```

## Step 13 : Blade & Templates

### resources/views/register.blade.php

```

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
<h2>Register</h2>

@if($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li style="color:red">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <label>Name:</label><br>
    <input type="text" name="name" value="{{ old('name') }}" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="{{ old('email') }}" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="password_confirmation" required><br><br>

    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
</body>
</html>

```

### resources/views/login.blade.php

```

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h2>Login</h2>

@if($errors->any())
    <ul>
        @foreach($errors->all() as $error)
            <li style="color:red">{{ $error }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>
</form>

<p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
</body>
</html>

```

### resources/views/dashboard.blade.php

```
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
<h2>Welcome, {{ auth()->user()->name }}</h2>

@if(auth()->user()->subscribed())
    <p style="color:green">You are subscribed!</p>
    <a href="{{ route('billing') }}">Manage Subscription</a>
@else
    <p style="color:red">You are not subscribed.</p>
    <a href="{{ route('subscribe') }}">Subscribe Now</a>
@endif

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Logout</button>
</form>
</body>
</html>

```


## STEP 14 : Running the App

### Finally run the development server:

```
php artisan serve

```

### Visit in browser:

```
http://localhost:8000

```

## So you can see this type Output :

### Register Page:


<img width="1915" height="964" alt="Screenshot 2026-01-08 173703" src="https://github.com/user-attachments/assets/8c051885-9a8b-4f72-8d6c-954a55bc7691" />


### Login Page:


<img width="1915" height="967" alt="Screenshot 2026-01-08 174007" src="https://github.com/user-attachments/assets/8bfbe6ba-1cee-4f34-a9b6-66b78b8dd131" />


### Dashboard Page :


<img width="1917" height="966" alt="Screenshot 2026-01-08 173715" src="https://github.com/user-attachments/assets/49a01ff8-39b6-4cb7-9711-6ad658539dd5" />


### Subscribe page:


<img width="1891" height="960" alt="Screenshot 2026-01-08 173842" src="https://github.com/user-attachments/assets/4e1c9ede-0acb-47cb-9191-2ac4259b9044" />


### after pay page:


<img width="1907" height="966" alt="Screenshot 2026-01-08 173901" src="https://github.com/user-attachments/assets/33c9b2b7-ac6c-4b77-936b-f4e5e75a8a3a" />






---


# Project Folder Structure:

```

PHP_Laravel12_Cashier/
├─ app/
│  ├─ Console/
│  ├─ Exceptions/
│  ├─ Http/
│  │  ├─ Controllers/
│  │  │  ├─ AuthController.php
│  │  │  └─ WebhookController.php
│  │  ├─ Middleware/
│  │  └─ Kernel.php
│  ├─ Models/
│  │  └─ User.php
│  ├─ Providers/
│  │  └─ AppServiceProvider.php
│  └─ ...
├─ bootstrap/
│  └─ cache/
├─ config/
│  ├─ app.php
│  ├─ cashier.php         # Optional Cashier config
│  ├─ database.php
│  └─ services.php        # Stripe keys added here
├─ database/
│  ├─ migrations/
│  │  ├─ xxxx_create_users_table.php
│  │  ├─ xxxx_create_subscriptions_table.php       # Cashier migration
│  │  └─ xxxx_create_subscription_items_table.php  # Cashier migration
│  ├─ seeders/
│  └─ factories/
├─ public/
│  ├─ index.php
│  └─ ...
├─ resources/
│  ├─ views/
│  │  ├─ register.blade.php
│  │  ├─ login.blade.php
│  │  └─ dashboard.blade.php
│  └─ ...
├─ routes/
│  └─ web.php
├─ storage/
│  ├─ app/
│  ├─ framework/
│  └─ logs/
├─ tests/
│  ├─ Feature/
│  └─ Unit/
├─ vendor/
├─ .env
├─ .gitignore
├─ artisan
├─ composer.json
├─ composer.lock
├─ package.json
├─ phpunit.xml
├─ README.md
└─ vite.config.js
```

