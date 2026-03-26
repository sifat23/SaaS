<?php

namespace App\Http\Controllers;

use App\Enums\ShopRegistrationStatus;
use App\Mail\MonthlyInvoiceMail;
use App\Mail\ShopCreatedMail;
// use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Services\ShopRegistrationService;
use App\Services\ShopService;
use App\Services\SubscriptionService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;

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
                $registrationID = $session->metadata->registration_id;
                $shopRegistration = $this->shopRegistrationRepo->findById($registrationID);

                $user = $this->userService->createUser($shopRegistration);
                $shop = $this->shopService->createShop($user, $registrationID);
                $this->userService->updateColumn($user, 'shop_id', $shop->id);

                SubscriptionService::createFreeSubscription($user);
                SubscriptionService::update($user, $shop);

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

        if ($event->type === 'invoice.payment_succeeded') {
            $invoice = $event->data->object;

            $user = Cashier::findBillable($invoice->customer);

            if ($user) {
                Mail::to($user->email)->queue(new MonthlyInvoiceMail($invoice, $user));
            } else {
                Log::warning('No user found for invoice payment', [
                    'customer_id' => $invoice->customer,
                    'invoice_id' => $invoice->id
                ]);
            }
        }
    }
}
