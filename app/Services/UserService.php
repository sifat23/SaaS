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

    public function findUserByStripeID(string $stripeID)
    {
        return $this->userRepo->findByKeyAndLock('stripe_id', $stripeID);
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

    public function updateUserData(User $user, array $data)
    {
        return $this->userRepo->update($user, $data);
    }

    public function updateColumn(User $user, string $columnName, string $value)
    {
        $this->userRepo->update($user, [
            $columnName => $value
        ]);

        return $user;
    }

    public function getDefaultSubscription($user)
    {
        return $user->resubscription('default');
    }
}
