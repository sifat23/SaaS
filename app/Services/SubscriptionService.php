<?php

namespace App\Services;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected SubscriptionRepositoryInterface $subscriptionRepo;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepo
    )
    {
        $this->subscriptionRepo = $subscriptionRepo;
    }

    public function createFreeSubscription($user)
    {
        $monthlyFee = config('services.stripe.monthly_fee');

        return $user->newSubscription('default', $monthlyFee)
            ->trialDays(30)
            ->create();
    }

    public function completeSubscription($subscriptionData): void
    {
        $stripeID = $subscriptionData['id'];
        $subscription = $this->subscriptionRepo->findByStripeId($stripeID);

        $nextBillingDate = null;

        if (isset($subscriptionData['current_period_end'])) {
            $nextBillingDate = $subscriptionData['current_period_end'];
        }

        foreach ($subscriptionData['items']['data'] as $item) {
            $itemNextBilling = isset($item['current_period_end'])
                ? Carbon::createFromTimestamp($item['current_period_end'])
                : $nextBillingDate;

            $itemStatCicle = isset($item['current_period_start'])
                ? Carbon::createFromTimestamp($item['current_period_start'])
                : null;

            if (!empty($itemNextBilling) && !empty($itemStatCicle)) {
                $this->subscriptionRepo->update($subscription, [
                    'next_billing_date' => $itemNextBilling,
                    'start_date' => $itemStatCicle
                ]);
            }
        }
    }
}
