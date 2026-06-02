<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    // Remove the constructor - middleware is now defined in routes
    
    // Show subscription plans
    public function plans()
    {
        return view('subscription.plans');
    }

    // Cancel subscription
    public function cancel(Request $request)
    {
        $user = $request->user();
        
        if ($user->subscribed()) {
            $user->subscription('default')->cancel();
            return redirect()->route('dashboard')->with('success', 'Subscription cancelled. You will have access until ' . $user->subscription('default')->ends_at->format('M d, Y'));
        }
        
        return redirect()->route('dashboard')->with('error', 'No active subscription found.');
    }

    // Resume subscription
    public function resume(Request $request)
    {
        $user = $request->user();
        
        if ($user->subscription('default')->onGracePeriod()) {
            $user->subscription('default')->resume();
            return redirect()->route('dashboard')->with('success', 'Subscription resumed successfully!');
        }
        
        return redirect()->route('dashboard')->with('error', 'Cannot resume subscription.');
    }

    // Update payment method
    public function updatePaymentMethod(Request $request)
    {
        $user = $request->user();
        
        return $user->redirectToBillingPortal(route('dashboard'));
    }

    // Show invoices
    public function invoices(Request $request)
    {
        $invoices = $request->user()->invoices();
        return view('subscription.invoices', compact('invoices'));
    }
}