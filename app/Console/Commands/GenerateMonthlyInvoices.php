<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
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


    public function handle()
    {
        $this->info('Starting monthly billing...');

        // $subscriptions = $this->subscriptionRepo->getActiveSubscriptions();
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
