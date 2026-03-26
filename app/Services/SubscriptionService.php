<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public static function createFreeSubscription($user)
    {
        $monthlyFee = config('services.stripe.monthly_fee');

        return $user->newSubscription('default', $monthlyFee)
            ->trialDays(30)
            ->create();
    }

    public static function update($user, $shop)
    {
        $subscription = $user->subscription('default');
        Log::info('subscription', [
            'tets' => $subscription
        ]);

        $subscription->update([
            'shop_id' => $shop->id,
            'start_date' => now(),
        ]); 
    }
}
