<?php

namespace App\Repositories\Eloquent;

use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ShopRepository implements ShopRepositoryInterface
{
    protected $model;

    public function __construct(Shop $shop)
    {
        $this->model = $shop;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Shop
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Shop
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $shop = $this->findById($id);
        return $shop->update($data);
    }

    public function delete(int $id): bool
    {
        $shop = $this->findById($id);
        return $shop->delete();
    }
}
