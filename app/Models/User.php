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

    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    // Check if user has active subscription
    public function hasActiveSubscription()
    {
        return $this->subscribed();
    }

    // Get subscription plan name
    public function getPlanAttribute()
    {
        if (!$this->subscribed()) {
            return 'No Plan';
        }
        
        $priceId = $this->subscription('default')->stripe_price;
        
        if ($priceId === env('STRIPE_PRICE_BASIC')) {
            return 'Basic Plan';
        } elseif ($priceId === env('STRIPE_PRICE_PRO')) {
            return 'Pro Plan';
        }
        
        return 'Unknown Plan';
    }

    // Check if on trial
    public function onTrial()
    {
        return $this->subscription('default') && $this->subscription('default')->onTrial();
    }
}