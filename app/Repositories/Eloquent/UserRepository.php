<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(string $key, string $value): ?User
    {
        return $this->model->where($key, $value)->first();
    }

    public function findById(int $id): ?User
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    public function update($user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        return $user->delete();
    }
}
