<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable();
            $table->foreignId('subscription_id')->nullable();
            $table->string('stripe_payment_intent')->nullable();
            $table->integer('amount');
            $table->string('currency', 10);
            $table->tinyInteger('type')->comment('1=Setup,2=Subscription');
            $table->tinyInteger('status')->comment('1=paid,0=pending,-1=failed');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
