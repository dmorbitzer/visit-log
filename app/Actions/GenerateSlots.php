<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\TrackingDay;
use Carbon\Carbon;

class GenerateSlots
{
    public function generate(Event $event, TrackingDay $day): array
    {
        $slots = [];
        $current = Carbon::parse($event->tracking_start);
        $end = Carbon::parse($event->tracking_end);

        while ($current < $end) {
            $next = $current->copy()->addMinutes($event->slot_interval_minutes);
            $slots[] = [
                'tracking_day_id' => $day->id,
                'slot_start' => $current->format('H:i'),
                'slot_end' => $next->format('H:i'),
                'visitor_count' => 0,
            ];
            $current = $next;
        }

        return $slots;
    }
}
