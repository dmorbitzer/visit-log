<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use App\Models\TrackingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeSlot>
 */
class TimeSlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tracking_day_id' => TrackingDay::factory(),
            'slot_start' => '12:00',
            'slot_end' => '12:30',
            'visitor_count' => fake()->numberBetween(0, 25),
        ];
    }
}
