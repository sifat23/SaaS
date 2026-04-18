<?php

namespace App\Http\Controllers\Stripe;

use App\Enums\ShopRegistrationStatus;
use App\Helpers\StripeHelper;
use App\Mail\ShopCreatedMail;
use App\Models\ShopRegistration;
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
use Stripe\PaymentIntent;

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

    public function handleCheckoutSessionCompleted($payload)
    {
        $session = $payload['data']['object'];

        $registrationID = $session['metadata']['registration_id'] ?? null;

        if (!$registrationID) {
            Log::warning('Webhook: checkout.session.completed missing registration_id');

            return response('Missing registration_id', 200);
        }

        $shopRegistration = ShopRegistration::find($registrationID);

        if (!$shopRegistration) {
            Log::warning('Webhook: ShopRegistration not found', ['id' => $registrationID]);

            return response('Registration not found', 200);
        }

        if ($shopRegistration->status == 1) {
            return response('Already processed', 200);
        }

        DB::beginTransaction();
        try {
            StripeHelper::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::retrieve($session['payment_intent']);
            $paymentMethodId = $paymentIntent->payment_method;
            $stripeCustomerId = $session['customer'];

            $user = $this->userService->createUser($shopRegistration);
            
            // $this->userService->updateUserData($user, [
            //     'stripe_id' => $stripeCustomerId
            // ]);


            // $user->createOrGetStripeCustomer([
            //     'email' => $user->email,
            //     'name' => $user->name,
            // ]);
            $user->updateDefaultPaymentMethod($paymentMethodId);
            // $user->updateDefaultPaymentMethodFromStripe();

            // $user->updateDefaultPaymentMethod($paymentMethodId);

            // $registrationID = $event['data']['object']['metadata']['registration_id'];
            // $shopRegistration = $this->shopRegistrationService->findShopRegistrationWithID($registrationID);


            $shop = $this->shopService->createShop($user, $registrationID);
            $this->userService->updateColumn($user, 'shop_id', $shop->id);

            $this->subscriptionService->createFreeSubscription($user, $paymentMethodId);
            //             SubscriptionService::update($user, $shop);

            $this->shopRegistrationService->updateShopRegistration($shopRegistration, [
                'status' => ShopRegistrationStatus::PAID
            ]);

            DB::commit();

            Mail::to($user->email)->queue(new ShopCreatedMail($user, $shop));

            Log::info('✅ Shop registration complete', [
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ checkout.session.completed failed', [
                'error'           => $e->getMessage(),
                'registration_id' => $registrationID,
            ]);

            return response('Processing error — logged', 200);
        }
    }

    public function handleCustomerSubscriptionCreated($payload)
    {
        $stripeSubscription = $payload['data']['object'];
        $stripeCustomerId   = $stripeSubscription['customer'];

        $user = $this->userService->findUserByStripeID($stripeCustomerId);

        if (!$user) {
            // User not created yet — handleCheckoutSessionCompleted will
            // handle subscription creation. Safe to ignore here.
            Log::info('handleCustomerSubscriptionCreated: user not found yet, skipping', [
                'customer' => $stripeCustomerId,
            ]);

            return response('OK', 200);
        }

        $this->subscriptionService->completeSubscription($stripeSubscription);

        return response('OK', 200);
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
        $stripeCustomerId = $subscriptionData['customer'];

        $user = $this->userService->findUserByStripeID($stripeCustomerId);

        if (!$user) {
            Log::warning('handleCustomerSubscriptionUpdated: user not found', [
                'customer' => $stripeCustomerId,
            ]);

            return response('OK', 200);
        }

        if ($subscriptionData['status'] === 'active') {
            // Fix: was referencing undefined $subscription variable
            $latestInvoiceId = $subscriptionData['latest_invoice'] ?? null;

            if ($latestInvoiceId) {
                $invoice = \Stripe\Invoice::retrieve($latestInvoiceId);

                if ($invoice->status === 'paid') {
                    $this->subscriptionService->processSubscriptionUpdate($subscriptionData, $user);

                    Log::info('✅ Subscription renewed', ['user_id' => $user->id]);
                }
            }
        }

        if (in_array($subscriptionData['status'], ['canceled', 'incomplete_expired', 'unpaid'])) {
            $this->shopService->suspendShop($user->shop_id);
            Log::info('🔴 Subscription canceled/expired', ['user_id' => $user->id]);
        }

        return response('OK', 200);

        // find user with customer id from payload
        // $user = $this->userService->findUserByStripeID($subscriptionData['customer']);


        // // Checking the subscription status
        // if ($subscriptionData['status'] === 'active') {
        //     if (isset($subscription['latest_invoice'])) {
        //         // You can optionally retrieve the invoice to double-check payment
        //         $invoice = \Stripe\Invoice::retrieve($subscription['latest_invoice']);
        //         if ($invoice->status === 'paid') {
        //             // process the subscription with the user;
        //             $result = $this->subscriptionService->processSubscriptionUpdate($subscriptionData, $user);

        //             // $this->info("✅ Payment confirmed & Subscription renewed for user {$user->id}");

        //             // $this->userService->updateUserData($user, [
        //             //     'stripe_status'          => $subscription['status'],
        //             //     'subscription_ends_at'   => $itemNextBilling
        //             // ]);

        //             $subscriptions = $this->userService->getDefaultSubscription($user);

        //             $this->subscriptionService->processSubscriptionUpdate($subscriptionData, $subscriptions);
        //         }
        //     }
        // }

        // // Handle cancellation or other status changes
        // if (in_array($subscription['status'], ['canceled', 'incomplete_expired', 'unpaid'])) {
        //     $this->info("Subscription ended for user {$user->id}");
        //     // Revoke access, etc.
        // }



        // Log::info('handle customer subscription updated', [
        //     'event' => $payload,
        // ]);
    }

    /**
     * Handle successful subscription renewal
     */
    public function handleInvoicePaymentSucceeded(array $payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $user       = $customerId ? $this->userService->findUserByStripeID($customerId) : null;

        if ($user) {
            $this->shopService->activateShop($user->shop_id);
            Log::info('✅ Invoice payment succeeded', ['user_id' => $user->id]);
        }

        return response('OK', 200);
    }

    /**
     * Handle failed renewal — THIS is what you need to debug
     */
    public function handleInvoicePaymentFailed(array $payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $user       = $customerId ? $this->userService->findUserByStripeID($customerId) : null;

        if ($user) {
            $this->shopService->suspendShop($user->shop_id);
            Log::error('❌ Invoice payment FAILED', ['user_id' => $user->id]);
            // TODO: Mail::to($user->email)->queue(new PaymentFailedMail($user));
        }

        return response('OK', 200);
    }

    /**
     * Trial ending soon reminder (3 days before)
     */
    public function handleCustomerSubscriptionTrialWillEnd(array $payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;
        $user       = $customerId ? $this->userService->findUserByStripeID($customerId) : null;

        if ($user) {
            Log::info('⏰ Trial ending soon', ['user_id' => $user->id]);
            // TODO: Mail::to($user->email)->queue(new TrialEndingMail($user));
        }

        return response('OK', 200);
    }
}
