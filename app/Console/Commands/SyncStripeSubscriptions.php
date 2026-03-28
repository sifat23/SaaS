<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncStripeSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync-stripe-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Stripe subscriptions daily (trial end, status, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
    }
}
