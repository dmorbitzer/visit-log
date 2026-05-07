<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Event',
            'type' => EventType::Recurring,
            'status' => EventStatus::Active,
            'recurrence_weekdays' => [0],
            'start_date' => now(),
            'end_date' => null,
            'tracking_start' => '12:00',
            'tracking_end' => '16:00',
            'slot_interval_minutes' => 60,
        ];
    }

    public function recurring(): static
    {
        return $this->state([
            'type' => EventType::Recurring,
            'recurrence_weekdays' => [0],
            'start_date' => now(),
            'end_date' => null,
        ]);
    }

    public function dateRange(): static
    {
        return $this->state([
            'type' => EventType::DateRange,
            'recurrence_weekdays' => null,
            'start_date' => now(),
            'end_date' => now()->addDays(3),
        ]);
    }

    public function customDays(): static
    {
        return $this->state([
            'type' => EventType::CustomDays,
            'recurrence_weekdays' => null,
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state([
            'status' => EventStatus::Archived,
        ]);
    }
}
