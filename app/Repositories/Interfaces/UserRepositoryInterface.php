<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
     public function all(): Collection;

     public function findById(int $id): ?User;

     public function create(array $data): User;

     public function update(User $user, array $data): bool;

     public function delete(int $id): bool;
}