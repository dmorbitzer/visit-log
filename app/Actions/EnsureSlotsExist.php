<?php

namespace App\Actions;

use App\Models\TimeSlot;
use App\Models\TrackingDay;

class EnsureSlotsExist
{
    public function __construct(
        private readonly GenerateSlots $generateSlots
    ) {}

    public function ensure(TrackingDay $day): void
    {
        if ($day->timeSlots()->exists()) {
            return;
        }

        $slots = $this->generateSlots->generate($day->event, $day);
        TimeSlot::insert($slots);
    }
}
