<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class AdvanceStripeTestClock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:advance-clock {clock_id} {--months=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance a Stripe Test Clock to simulate renewal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $stripe = new StripeClient(env('STRIPE_SECRET'));

        $clockId = $this->argument('clock_id');
        $months  = (int) $this->option('months');

        $this->info("Advancing test clock {$clockId} by {$months} month(s)...");

        $newTime = Carbon::now()->addMonths($months)->timestamp;

        $stripe->testHelpers->testClocks->advance($clockId, [
            'frozen_time' => $newTime,
        ]);

        $this->info('✅ Test clock advanced successfully!');
        $this->warn('Watch your webhook logs now. You should receive:');
        $this->warn('• invoice.payment_succeeded');
        $this->warn('• customer.subscription.updated');
        $this->warn('• test_helpers.test_clock.ready');

        return Command::SUCCESS;
    }
}
