<?php

namespace App\Http\Controllers;

use App\Models\ShopRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();

        $event = \Stripe\Event::constructFrom(
            json_decode($payload, true)
        );

        if ($event->type === 'checkout.session.completed') {

            $session = $event->data->object;

            //ToDo
            //every payment creates a session and whether it is setup fee or monthly subscription
            //Need to work this this segment latter

            // $registration = ShopRegistration::where(
            //     'stripe_session_id',
            //     $session->id
            // )->first();
        }
    }
}
