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

    public function findShopRegistrationWithID(string $id)
    {
        return $this->shopRegistrationRepo->findById($id);
    }

    public function temporaryRegistration(ShopRegistrationRequest $request)
    {
        return $this->shopRegistrationRepo->create($request->validated());
    }

    public function updateShopRegistration (ShopRegistration $shop, $data)
    {
        return $this->shopRegistrationRepo->update($shop, $data);
    }

    public function updateStatus (ShopRegistration $shop, $status)
    {
        return $this->shopRegistrationRepo->update($shop, [
            'status' => $status
        ]);
    }

    
}
