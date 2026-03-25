<?php

namespace App\Repositories\Interfaces;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Collection;

interface ShopRepositoryInterface
{
    public function all(): Collection;

    public function find(string $key, string $value): ?Shop;
    
    public function findById(int $id): ?Shop;

    public function create(array $data): Shop;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
