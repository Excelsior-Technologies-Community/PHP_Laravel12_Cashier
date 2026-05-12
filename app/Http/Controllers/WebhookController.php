<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class WebhookController extends CashierWebhookController
{
    public function handleWebhook(Request $request)
    {
        Log::info('Stripe Webhook Received: ', $request->all());

        return parent::handleWebhook($request);
    }

    protected function handleCustomerSubscriptionCreated(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::info("Subscription Created for user: " . $user->email);
        }

        return parent::handleCustomerSubscriptionCreated($payload);
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::info("Subscription Updated for user: " . $user->email);
        }

        return parent::handleCustomerSubscriptionUpdated($payload);
    }

    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::info("Subscription Cancelled for user: " . $user->email);
        }

        return parent::handleCustomerSubscriptionDeleted($payload);
    }

    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::info("Payment Succeeded and Invoice Generated for: " . $user->email);
        }

        return parent::handleInvoicePaymentSucceeded($payload);
    }

    protected function handleInvoicePaymentFailed(array $payload)
    {
        $user = $this->getUserByStripeId($payload['data']['object']['customer']);
        if ($user) {
            Log::error("Payment Failed for user: " . $user->email);
        }

        return parent::handleInvoicePaymentFailed($payload);
    }
}