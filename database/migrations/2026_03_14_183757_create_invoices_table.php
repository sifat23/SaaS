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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable();
            $table->foreignId('subscription_id')->nullable();
            $table->integer('amount');
            $table->string('currency', 10);
            $table->string('stripe_invoice_id')->nullable();
            $table->tinyInteger('status')->comment('1=paid,0=void,-1=unpaid');
            $table->date('billing_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
