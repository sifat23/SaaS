<?php 

namespace App\Services;

use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\ShopRepositoryInterface;
use App\Http\Requests\ShopRegistrationRequest;
use App\Models\ShopRegistration;

class ShopRegistrationService
{
    protected $shopRegistrationRepo;
    protected $userRepo;
    protected $shopRepo;

     public function __construct(
        ShopRegistrationRepositoryInterface $shopRegistrationRepo,
        UserRepositoryInterface $userRepo,
        ShopRepositoryInterface $shopRepo,
    ) {
        $this->shopRegistrationRepo = $shopRegistrationRepo;
        $this->userRepo = $userRepo;
        $this->shopRepo = $shopRepo;
    }

    public function temporaryRegistration(ShopRegistrationRequest $request)
    {
        return $this->shopRegistrationRepo->create([
            'owner_email' => $request->owner_email,
            'owner_name' => $request->owner_name,
            'shop_name' => $request->shop_name,
            'password' => $request->password,
        ]);
    }

    public function updateColumn (ShopRegistration $shop, $key, $value)
    {
        return $this->shopRegistrationRepo->update($shop, [
            $key => $value
        ]);
    }

    public function updateStatus (ShopRegistration $shop, $status)
    {
        return $this->shopRegistrationRepo->update($shop, [
            'status' => $status
        ]);
    }
}
