<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $subscriptionService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly billing for subscriptions';

    /**
     * Execute the console command.
     */

    public function __construct(
        SubscriptionService $subscriptionService
    ) {
        parent::__construct(); 
        $this->subscriptionService = $subscriptionService;
    }

    public function handle()
    {
        $this->info('Starting monthly billing...');

        // $subscriptions = $this->subscriptionService->getActiveSubscriptions();
        // if ($subscriptions->isEmpty()) {
        //     $this->info('✅ No subscriptions due for billing today.');
        //     return;
        // }

        // $this->info("Found {$subscriptions->count()} subscription(s) to bill.");

        // $result = $this->subscriptionService->processSubscription($subscriptions);
        

        // $billableNow = $subscriptions->filter(function ($sub) {

        //     if (!$sub->valid()) {
        //         return false;
        //     }

        //     if (! $sub->renews_at) {
        //         return false;
        //     }

        //     return $sub->renews_at->lessThanOrEqualTo(now());
        // });
    }
}
