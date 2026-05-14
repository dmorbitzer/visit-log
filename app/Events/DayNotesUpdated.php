<?php

namespace App\Events;

use App\Models\TrackingDay;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class DayNotesUpdated implements ShouldBroadcastNow
{
    public function __construct(
        public readonly TrackingDay $day,
    ) {}

    public function broadcastAs(): string
    {
        return 'DayNotesUpdated';
    }

    public function broadcastOn(): Channel
    {
        return new Channel("day.{$this->day->id}");
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->day->id,
            'notes' => $this->day->notes,
        ];
    }
}
