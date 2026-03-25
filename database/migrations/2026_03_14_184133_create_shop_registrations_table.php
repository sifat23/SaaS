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
        Schema::create('shop_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name', 200);
            $table->string('owner_email', 200)->unique();
            $table->string('password', 200);
            $table->string('shop_name', 200);
            $table->string('stripe_session_id')->nullable();
            $table->tinyInteger('status')->comment('pending=0, paid=1, failed=-1')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_registrations');
    }
};
