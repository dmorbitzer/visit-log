<?php

namespace App\Events;

use App\Models\TimeSlot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class SlotUpdated implements ShouldBroadcastNow
{
    public function __construct(
        public readonly TimeSlot $slot,
    ) {}

    public function broadcastAs(): string
    {
        return 'SlotUpdated';
    }

    public function broadcastOn(): Channel
    {
        return new Channel("slot.{$this->slot->id}");
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->slot->id,
            'visitor_count' => $this->slot->visitor_count,
        ];
    }
}
