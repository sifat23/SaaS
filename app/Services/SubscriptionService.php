<?php

namespace App\Services;

use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    protected $subscriptionRepo;
    protected $userRepo;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepo,
        UserRepositoryInterface $userRepo,
    ) {
        $this->subscriptionRepo = $subscriptionRepo;
        $this->userRepo = $userRepo;
    }

    public function createFreeSubscription($user)
    {
        $monthlyFee = config('services.stripe.monthly_fee');

        return $user->newSubscription('default', $monthlyFee)
            ->trialDays(30)
            ->create();
    }

    public function updateSubscriptionModel($subscription, $data)
    {
        return $this->subscriptionRepo->update($subscription, $data);
    }

    public function completeSubscription($subscriptionData): void
    {
        $stripeID = $subscriptionData['id'];
        $subscription = $this->subscriptionRepo->find('stripe_id', $stripeID);

        [$itemNextBilling, $itemStatCycle] = $this->findPeriodCycle($subscriptionData);

        if (!empty($itemNextBilling) && !empty($itemStatCycle)) {
            $this->subscriptionRepo->update($subscription, [
                'next_billing_date' => $itemNextBilling,
                'start_date' => $itemStatCycle
            ]);
        }
    }

    public function findPeriodCycle($subscriptionData)
    {
        [$itemNextBilling, $itemStatCycle] = null;

        $nextBillingDate = null;

        if (isset($subscriptionData['current_period_end'])) {
            $nextBillingDate = $subscriptionData['current_period_end'];
        }

        foreach ($subscriptionData['items']['data'] as $item) {
            $itemNextBilling = isset($item['current_period_end'])
                ? Carbon::createFromTimestamp($item['current_period_end'])
                : $nextBillingDate;

            $itemStatCycle = isset($item['current_period_start'])
                ? Carbon::createFromTimestamp($item['current_period_start'])
                : null;
        }

        return [$itemNextBilling, $itemStatCycle];
    }

    public function processSubscriptionUpdate($subscriptionData, $user)
    {
        [$itemNextBilling, $itemStatCycle] = $this->findPeriodCycle($subscriptionData);

        DB::beginTransaction();
        try {
            $this->userRepo->update($user, [
                'stripe_status'          => $subscriptionData['status'],
                'subscription_ends_at'   => $itemNextBilling
            ]);
    
            $userSubscription = $this->userRepo->getDefaultSubscription($user);
    
            $this->subscriptionRepo->update($userSubscription, [
                'next_billing_date' => $itemNextBilling,
                'start_date' => $itemStatCycle
            ]);
            
            DB::commit();
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            dd($e);
        }
    }
}
