<?php

namespace App\Services;

use App\Enums\ShopStatus;
use App\Helpers\SlugHelper;
use App\Models\Shop;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Repositories\Interfaces\ShopRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

class ShopService
{
    protected $userRepo;
    protected $shopRegistrationRepo;
    protected $shopRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        ShopRegistrationRepositoryInterface $shopRegistrationRepo,
        ShopRepositoryInterface $shopRepo,
    ) {
        $this->userRepo = $userRepo;
        $this->shopRegistrationRepo = $shopRegistrationRepo;
        $this->shopRepo = $shopRepo;
    }

    public function createShop($user, $registrationID)
    {
        $registration = $this->shopRegistrationRepo->findById($registrationID);

        return $this->shopRepo->create([
            'name' => $registration->shop_name,
            'owner_id' => $user->id,
            'slug' => SlugHelper::generate(Shop::class, $registration->shop_name),
            'email' => $registration->owner_email,
            'status' => ShopStatus::ACTIVE,
            'trial_ends_at' => now()->addMonth()
        ]);
    }

    public function suspendShop(int $shopId): bool
    {
        $shop = $this->shopRepo->find('id', $shopId);

        return $this->shopRepo->update($shop, [
            'status' =>  ShopStatus::SUSPENDED
        ]);
    }

    public function activateShop(int $shopId): bool
    {
        $shop = $this->shopRepo->find('id', $shopId);

        return $this->shopRepo->update($shop, [
            'status' =>  ShopStatus::ACTIVE
        ]);
     }
}
