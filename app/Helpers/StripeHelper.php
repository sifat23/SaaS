<?php

namespace App\Helpers;

use App\Models\ShopRegistration;
use Carbon\Carbon;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeHelper
{
    public static function setApiKey(?string $key = null)
    {
        return Stripe::setApiKey($key ?? config('services.stripe.secret'));
    }

    // public static function getSetupSession(ShopRegistration $registration)
    // {
    //     $setupFee = config('services.stripe.setup_fee');

    //     self::setApiKey();

    //     return Session::create([
    //         'mode' => 'payment',
    //         'customer_email' => $registration->owner_email,

    //         'payment_intent_data' => [
    //             'setup_future_usage' => 'off_session',
    //         ],

    //         'metadata' => [
    //             'registration_id' => $registration->id
    //         ],

    //         'line_items' => [[
    //             'price' => $setupFee,
    //             'quantity' => 1,
    //         ]],

    //         'success_url' => route('shop.registration.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
    //         'cancel_url' => route('setup.payment.canceled') . '?session_id={CHECKOUT_SESSION_ID}',
    //     ]);
    // }

    public function getSessionDetails(string $sessionID): Session
    {
        self::setApiKey();

        return \Stripe\Checkout\Session::retrieve($sessionID);

    }

    public function createTestSubscriptionWithClock()
    {
        $stripe = new StripeClient(config('services.stripe.secret'));

        $testToken = 'tok_visa';

        $testClock = $stripe->testHelpers->testClocks->create([
            'frozen_time' => Carbon::now()->timestamp,
            'name'        => 'Monthly Renewal Test - ' . now()->format('Y-m-d H:i'),
        ]);

        $customer = $stripe->customers->create([
            'name'       => 'Test User - Renewal Simulation',
            'email'      => 'test-renewal-' . time() . '@example.com',
            'test_clock' => $testClock->id,                 // ← This is the key
        ]);

        // $paymentMethod = $stripe->paymentMethods->create([
        //     'type' => 'card',
        //     'card' => [
        //         'number'    => '4242424242424242',   // Always succeeds
        //         'exp_month' => 12,
        //         'exp_year'  => Carbon::now()->addYear()->year,
        //         'cvc'       => '123',
        //     ],
        // ]);

        $paymentMethod = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
                'token' => $testToken
            ],
        ]);

        $stripe->paymentMethods->attach(
            $paymentMethod->id,
            ['customer' => $customer->id]
        );

        $stripe->customers->update($customer->id, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethod->id,
            ],
        ]);

        $subscription = $stripe->subscriptions->create([
            'customer' => $customer->id,
            'items'    => [
                ['price' => env('MONTHLY_FEE')], // your monthly price ID
            ],
            'payment_behavior' => 'default_incomplete', // or 'allow_incomplete'
            // You can also set trial, proration, etc. here
        ]);

        return [
            'test_clock'   => $testClock,
            'customer'     => $customer,
            'subscription' => $subscription,
        ];
    }
}
