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
