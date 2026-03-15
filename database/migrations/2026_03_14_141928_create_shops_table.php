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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('slug', 300);
            $table->string('email', 200)->unique();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->tinyInteger('status')->comment("1=active,0=suspended,-1=cancel");
            $table->timestamp("trial_ends_at");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
