<?php

namespace App\Http\Controllers\Stripe;

use App\Enums\ShopRegistrationStatus;
use App\Mail\ShopCreatedMail;
use App\Services\ShopRegistrationService;
use App\Services\ShopService;
use App\Services\SubscriptionService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Events\WebhookReceived;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;


class WebhookController extends CashierWebhookController
{
    protected ShopRegistrationService $shopRegistrationService;
    protected UserService $userService;
    protected ShopService $shopService;
    protected SubscriptionService $subscriptionService;

    public function __construct(
        ShopRegistrationService $shopRegistrationService,
        UserService $userService,
        ShopService $shopService,
        SubscriptionService $subscriptionService
    ) {
        $this->userService = $userService;
        $this->shopService = $shopService;
        $this->shopRegistrationService = $shopRegistrationService;
        $this->subscriptionService = $subscriptionService;
    }


    // public function handleWebhook(Request $request)
    // {
    //     $payload = json_decode($request->getContent(), true);
    //     $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

    //     Log::info('handle web hook', [
    //         'payload' => $payload,
    //         'method' => $method
    //     ]);

    //     WebhookReceived::dispatch($payload);

    //     if (method_exists($this, $method)) {
    //         $this->setMaxNetworkRetries();

    //         $response = $this->{$method}($payload);

    //         WebhookHandled::dispatch($payload);

    //         return $response;
    //     }

    //     return $this->missingMethod($payload);
    // }

    public function handleCheckoutSessionCompleted($event)
    {
        DB::beginTransaction();
        try {
            $registrationID = $event['data']['object']['metadata']['registration_id'];
            $shopRegistration = $this->shopRegistrationService->findShopRegistrationWithID($registrationID);

            $user = $this->userService->createUser($shopRegistration);
            $shop = $this->shopService->createShop($user, $registrationID);
            $this->userService->updateColumn($user, 'shop_id', $shop->id);

            $this->subscriptionService->createFreeSubscription($user);
            //             SubscriptionService::update($user, $shop);

            $this->shopRegistrationService->updateShopRegistration($shopRegistration, [
                'status' => ShopRegistrationStatus::PAID
            ]);

            DB::commit();

            Mail::to($user->email)->queue(new ShopCreatedMail($user, $shop));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info('checkout session complete error', [
                'e' => $e
            ]);
        }
    }

    public function handleCustomerSubscriptionCreated($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $this->subscriptionService->completeSubscription($stripeSubscription);
    }

    public function handleTestHelpersTestClockAdvancing($payload)
    {
        //  $subscription = $payload['data']['object'];

        // Log::info('Webhook: customer: ', [
        //     'this is where i wrote customer' => $subscription
        // ]);
        // return;
    }

    public function handleCustomerSubscriptionUpdated($payload)
    {
        $subscriptionData = $payload['data']['object'];

        // find user with customer id from payload
        $user = $this->userService->findUserByStripeID($subscriptionData['customer']);


        // Checking the subscription status
        if ($subscriptionData['status'] === 'active') {
            if (isset($subscription['latest_invoice'])) {
                // You can optionally retrieve the invoice to double-check payment
                $invoice = \Stripe\Invoice::retrieve($subscription['latest_invoice']);
                if ($invoice->status === 'paid') {
                    // process the subscription with the user;
                    $result = $this->subscriptionService->processSubscriptionUpdate($subscriptionData, $user);

                    // $this->info("✅ Payment confirmed & Subscription renewed for user {$user->id}");

                    // $this->userService->updateUserData($user, [
                    //     'stripe_status'          => $subscription['status'],
                    //     'subscription_ends_at'   => $itemNextBilling
                    // ]);

                    $subscriptions = $this->userService->getDefaultSubscription($user);

                    $this->subscriptionService->processSubscriptionUpdate($subscriptionData, $subscriptions);
                }
            }
        }

        // // Handle cancellation or other status changes
        // if (in_array($subscription['status'], ['canceled', 'incomplete_expired', 'unpaid'])) {
        //     $this->info("Subscription ended for user {$user->id}");
        //     // Revoke access, etc.
        // }



        // Log::info('handle customer subscription updated', [
        //     'event' => $payload,
        // ]);
    }
}
