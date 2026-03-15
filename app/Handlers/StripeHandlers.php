<?php

namespace App\Handlers;

use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeHandlers
{
    public function setApiKey()
    {
        return Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function getSetupSession(
        int $amount,
        string $email
    ) {
        return Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'customer_email' => $email, // pass email here
            'line_items' => [[
                'price_data' => [
                    'currency' => 'bdt',
                    'product_data' => [
                        'name' => 'POS Setup Fee',
                    ],
                    // 'unit_amount' => 2000000, // 20000 BDT
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('shop.registration.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/registration-cancel'),
        ]);
    }
}
