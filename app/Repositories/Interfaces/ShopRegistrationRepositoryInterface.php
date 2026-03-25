<?php

namespace App\Repositories\Interfaces;

use App\Models\ShopRegistration;
use Illuminate\Database\Eloquent\Collection;

interface ShopRegistrationRepositoryInterface
{
    public function all(): Collection;

    public function find(string $key, string $value): ?ShopRegistration;

    public function findById(int $id): ?ShopRegistration;

    public function create(array $data): ShopRegistration;

    public function update(ShopRegistration $shopRegistration, array $data): bool;

    public function delete(int $id): bool;
}
