<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Laravel\Cashier\Subscription;

class SubscriptionRepository extends BaseRepository implements SubscriptionRepositoryInterface
{
    public function __construct(Subscription $subscription)
    {
        parent::__construct($subscription);
    }
}
