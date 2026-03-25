<?php

namespace App\Http\Controllers;

use App\Enums\ShopStatus;
use App\Enums\UserStatus;
use App\Handlers\RedisHandlers;
use App\Handlers\SlugHandler;
use App\Handlers\StripeHandlers;
use App\Http\Requests\ShopRegistrationRequest;
use App\Models\Shop;
use App\Models\ShopRegistration;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Repositories\Interfaces\ShopRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\ShopRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Config;

class ShopRegistrationController extends Controller
{
    protected $shopRegistrationService;
    protected $userRepo;
    protected $shopRepo;

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
        $newRegistration = $this->shopRegistrationService->temporaryRegistration($request);
        
        $setupFee = Config::get('app.shop_setup_fee');

        $handleStripe = new StripeHandlers();
        $handleStripe->setApiKey();
        $session = $handleStripe->getSetupSession($setupFee, $request->email);


        $update = $this->shopRegistrationService
            ->updateColumn($newRegistration, 'stripe_session_id', $session->id);


        // $updateReg$newRegistration->update([
        //     'stripe_session_id' => $session->id
        // ]);

        // RedisHandlers::setAData($session->id, $chunk);

        return Inertia::location($session->url);
    }

    public function stripeSuccess(Request $request)
    {
        $sessionID = $request->input('session_id');

        $sessionData = RedisHandlers::getData($sessionID);
        if ($sessionData === null) {
            return Inertia::location(route('shop.registration'));
        }

        $shopRegistration = ShopRegistration::query()->where('stripe_session_id', $sessionID)->first();

        DB::beginTransaction();
        try {
            $user = $this->userRepo->create([
                'name' => $sessionData->owners_name,
                'email' => $sessionData->owners_email,
                'password' => $sessionData->password,
                'status' => UserStatus::ACTIVE
            ]);

            $shop = $this->shopRepo->create([
                'name' => $shopRegistration->shop_name,
                'slug' => SlugHandler::generate(Shop::class, $shopRegistration->shop_name),
                'email' => $shopRegistration->email,
                'owner_id' => $user->id,
                'trial_ends_at' => now()->addMonth(),
                'status' => ShopStatus::ACTIVE
            ]);

            $this->userRepo->update($user, [
                'shop_id' => $shop->id
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return Inertia::location(route('login'));
    }
}
