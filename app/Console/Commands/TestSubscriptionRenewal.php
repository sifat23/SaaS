<?php

namespace App\Console\Commands;

use App\Helpers\StripeHelper;
use Illuminate\Console\Command;

class TestSubscriptionRenewal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:test-renewal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test subscription with Stripe Test Clock and simulate renewal';

    /**
     * Execute the console command.
     */
    public function handle(StripeHelper $stripeTestService)
    {
        $this->info('Creating test subscription with Test Clock...');

        $result = $stripeTestService->createTestSubscriptionWithClock();

        $this->table(
            ['Item', 'ID', 'Details'],
            [
                ['Test Clock',   $result['test_clock']->id, $result['test_clock']->name],
                ['Customer',     $result['customer']->id,   $result['customer']->email],
                ['Subscription', $result['subscription']->id, 'Status: ' . $result['subscription']->status],
            ]
        );  

        $this->info('✅ Test subscription created successfully!');
        $this->warn('Now run: php artisan stripe:advance-clock ' . $result['test_clock']->id);
        
        return Command::SUCCESS;
    }
}
