<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // Stripe event ID
            $table->string('event_type');
            $table->timestamp('processed_at');
            $table->index(['event_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_events');
    }
};
