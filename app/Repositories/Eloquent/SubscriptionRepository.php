<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Laravel\Cashier\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{
    protected Subscription $model;

    public function __construct(Subscription $subscription)
    {
        $this->model = $subscription;
    }


    public function getActiveSubscriptions()
    {
        return $this->model->where('stripe_status', '')
            ->where('stripe_status', 'active')
            ->orWhere('stripe_status', 'trialing')
            ->get();
    }

    public function findByStripeId(string $id): ?Subscription
    {
        return $this->model->query()->where('stripe_id', $id)->first();
    }

    public function update($subscription, array $data): bool
    {
        return $subscription->update($data);
    }
}
