<?php

namespace App\Http\Controllers\Stripe;

use App\Enums\ShopRegistrationStatus;
use App\Mail\ShopCreatedMail;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Services\ShopRegistrationService;
use App\Services\ShopService;
use App\Services\SubscriptionService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;


class WebhookController extends CashierWebhookController
{
    protected ShopRegistrationRepositoryInterface $shopRegistrationRepo;
    protected ShopRegistrationService $shopRegistrationService;
    protected UserService $userService;
    protected ShopService $shopService;
    protected SubscriptionService $subscriptionService;

    public function __construct(
        ShopRegistrationRepositoryInterface $shopRegistrationRepo,
        ShopRegistrationService $shopRegistrationService,
        UserService $userService,
        ShopService $shopService,
        SubscriptionService $subscriptionService
    ) {
        $this->userService = $userService;
        $this->shopService = $shopService;
        $this->shopRegistrationRepo = $shopRegistrationRepo;
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
            $shopRegistration = $this->shopRegistrationRepo->findById($registrationID);

            $user = $this->userService->createUser($shopRegistration);
            $shop = $this->shopService->createShop($user, $registrationID);
            $this->userService->updateColumn($user, 'shop_id', $shop->id);

            $this->subscriptionService->createFreeSubscription($user);
//             SubscriptionService::update($user, $shop);

            $this->shopRegistrationRepo->update($shopRegistration, [
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
        Log::info('handle customer subscription created', [
            'event' => $payload,
            'data' => $payload['data']['object'],
        ]);



        $stripeSubscription = $payload['data']['object'];
        $this->subscriptionService->completeSubscription($stripeSubscription);
    }
}
