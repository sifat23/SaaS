<?php

namespace App\Repositories\Eloquent;

use App\Models\ProcessedEvent;
use App\Repositories\Interfaces\ProcessedEventRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ProcessedEventRepository extends BaseRepository implements ProcessedEventRepositoryInterface
{
    public function __construct(ProcessedEvent $model)
    {
        parent::__construct($model);
    }

    public function hasBeenProcessed(string $eventId): bool
    {
        return $this->model->where('event_id', $eventId)->exists();
    }

    public function markAsProcessed(string $eventId, string $eventType): Model
    {
        return $this->model->create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'processed_at' => now(),
        ]);
    }
}
