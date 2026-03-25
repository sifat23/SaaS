<?php

namespace App\Http\Controllers;

use App\Enums\ShopRegistrationStatus;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Services\ShopRegistrationService;
use App\Services\ShopService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected $shopRegistrationRepo;
    protected $shopRegistrationService;
    protected $userService;
    protected $shopService;

    public function __construct(
        ShopRegistrationRepositoryInterface $shopRegistrationRepo,
        ShopRegistrationService $shopRegistrationService,
        UserService $userService,
        ShopService $shopService,
    ) {
        $this->userService = $userService;
        $this->shopService = $shopService;
        $this->shopRegistrationRepo = $shopRegistrationRepo;
        $this->shopRegistrationService = $shopRegistrationService;
    }


    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();

        $event = \Stripe\Event::constructFrom(
            json_decode($payload, true)
        );

        $session = $event->data->object;

        if ($event->type === 'checkout.session.completed') {
            DB::beginTransaction();
            try {
                Log::info('stripe payment processing', [
                    'session_id' => $session->id
                ]);

                $registrationID = $session->metadata->registration_id;
                Log::info('ID', [
                    'registration_id' => $session->id
                ]);

                $shopRegistration = $this->shopRegistrationRepo->findById($registrationID);

                Log::info('registration', [
                    'data' => $shopRegistration
                ]);


                $user = $this->userService->createUser($shopRegistration);
                $shop = $this->shopService->createShop($user, $registrationID);
                $this->userService->updateColumn($user, 'shop_id', $shop->id);

                $this->shopRegistrationRepo->update($shopRegistration, [
                    'status' => ShopRegistrationStatus::PAID
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::info('checkout session complete error', [
                    'e' => $e
                ]);
            }
        }
    }
}
