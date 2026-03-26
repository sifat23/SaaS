<?php

namespace App\Helpers;

use App\Models\ShopRegistration;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeHelper
{
    public static function setApiKey()
    {
        return Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public static function getSetupSession(ShopRegistration $registration) 
    {   
        $setupFee = config('services.stripe.setup_fee');

        self::setApiKey();

        return Session::create([
            'mode' => 'payment',
            'customer_email' => $registration->owner_email,
            
            'metadata' => [
                'registration_id' => $registration->id
            ],
            
            'line_items' => [[
                'price' => $setupFee,
                'quantity' => 1,
            ]],
            
            'success_url' => route('shop.registration.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/registration-cancel'),

            // 'invoice_creation' => [
            //     'enable' => true,
            //     'invoice_data' => [
            //         'description' => 'Payment for shop registration - ' . $registration->shop_name,
            //     ]
            // ]
        ]);
    }
}
