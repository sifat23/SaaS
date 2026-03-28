<?php

namespace App\Repositories\Interfaces;

use Laravel\Cashier\Subscription;

interface SubscriptionRepositoryInterface
{
    public function getActiveSubscriptions();

    public function findByStripeId(string $id): ?Subscription;

    public function update($subscription, array $data): bool;
}
