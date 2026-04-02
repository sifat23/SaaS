<?php

namespace App\Repositories\Interfaces;

use App\Models\ProcessedEvent;
use Illuminate\Database\Eloquent\Model;

interface ProcessedEventRepositoryInterface
{
    public function hasBeenProcessed(string $eventId): bool;

    public function markAsProcessed(string $eventId, string $eventType): Model;
}
