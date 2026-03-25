<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserService
{
    protected $userRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
    ) {
        $this->userRepo = $userRepo;
    }

    public function createUser($registration)
    {
        return $this->userRepo->create([
            'name' => $registration->owner_name,
            'email' => $registration->owner_email,
            'password' => $registration->password,
            'status' => UserStatus::ACTIVE
        ]);
    }

    public function updateColumn(User $user, string $columnName, string $value)
    {
        $this->userRepo->update($user, [
            $columnName => $value
        ]);

        return $user;
    }
}
