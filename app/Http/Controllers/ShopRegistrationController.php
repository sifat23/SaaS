<?php

namespace App\Http\Controllers;

use App\Enums\ShopStatus;
use App\Enums\UserStatus;
use App\Handlers\RedisHandlers;
use App\Handlers\SlugHandler;
use App\Handlers\StripeHandlers;
use App\Helpers\StripeHelper;
use App\Http\Requests\ShopRegistrationRequest;
use App\Models\Shop;
use App\Models\ShopRegistration;
use App\Services\ShopRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Checkout;

class ShopRegistrationController extends Controller
{
    protected $shopRegistrationService;

    // Inject the UserRepositoryInterface here too!
    public function __construct(
        ShopRegistrationService $shopRegistrationService
    ) {
        $this->shopRegistrationService = $shopRegistrationService;
    }


    public function index(Request $request)
    {
        return Inertia::render('Auth/ShopRegistration');
    }

    public function store(ShopRegistrationRequest $request)
    {
        DB::beginTransaction();
        try {
            $newRegistration = $this->shopRegistrationService->temporaryRegistration($request);

            StripeHelper::setApiKey();
            $customerParams = [
                'email' => $newRegistration->owner_email,
                'name'  => $newRegistration->owner_name,
            ];
            $testClockId = config('services.stripe.test_clock_id');
            if ($testClockId) {
                $customerParams['test_clock'] = $testClockId;
            }

            $stripeCustomer = \Stripe\Customer::create($customerParams);


            $checkout = Checkout::guest()->create(
                [
                    config('services.stripe.setup_fee') => 1
                ],
                [
                    'customer'    => $stripeCustomer->id,  // ← changed
                    'payment_intent_data' => [
                        'setup_future_usage' => 'off_session',
                    ],

                    'metadata' => [
                        'registration_id' => $newRegistration->id,
                        'stripe_customer_id' => $stripeCustomer->id,
                    ],

                    'success_url' => route('shop.registration.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('setup.payment.canceled') . '?session_id={CHECKOUT_SESSION_ID}',
                ]
            );



            // $session = StripeHelper::getSetupSession($newRegistration);

            $this->shopRegistrationService->updateShopRegistration($newRegistration, [
                'stripe_session_id' => $checkout->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Shop registration checkout error', ['error' => $e->getMessage()]);

            return back()->withErrors(['message' => 'Payment setup failed. Please try again.']);
        }

        return Inertia::location($checkout->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return Inertia::location(route('shop.registration'));
        }

        return Inertia::render('Auth/ShopRegistrationSuccess', [
            'session_id' => $sessionId,
        ]);

        // $sessionData = RedisHandlers::getData($sessionID);
        // if ($sessionData === null) {
        //     return Inertia::location(route('shop.registration'));
        // }



        // $shopRegistration = ShopRegistration::query()->where('stripe_session_id', $sessionID)->first();

        // DB::beginTransaction();
        // try {
        //     $user = $this->userRepo->create([
        //         'name' => $sessionData->owners_name,
        //         'email' => $sessionData->owners_email,
        //         'password' => $sessionData->password,
        //         'status' => UserStatus::ACTIVE
        //     ]);

        //     $shop = $this->shopRepo->create([
        //         'name' => $shopRegistration->shop_name,
        //         'slug' => SlugHandler::generate(Shop::class, $shopRegistration->shop_name),
        //         'email' => $shopRegistration->email,
        //         'owner_id' => $user->id,
        //         'trial_ends_at' => now()->addMonth(),
        //         'status' => ShopStatus::ACTIVE
        //     ]);

        //     $this->userRepo->update($user, [
        //         'shop_id' => $shop->id
        //     ]);

        //     DB::commit();
        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     throw $th;
        // }

        // return Inertia::location(route('login'));
    }

    public function completeShopRegistration($registrationID) {}
}
