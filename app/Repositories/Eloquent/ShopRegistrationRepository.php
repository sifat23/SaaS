<?php

namespace App\Repositories\Eloquent;

use App\Models\ShopRegistration;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShopRegistrationRepository implements ShopRegistrationRepositoryInterface
{
    protected $model;

    public function __construct(ShopRegistration $shopRegistration)
    {
        $this->model = $shopRegistration;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?ShopRegistration
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): ShopRegistration
    {
        return $this->model->create($data);
    }

    public function update($shopRegistration, array $data): bool
    {
        return $shopRegistration->update($data);
    }

    public function delete(int $id): bool
    {
        $shop = $this->findById($id);
        return $shop->delete();
    }
}
