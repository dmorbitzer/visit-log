<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\TrackingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackingDay>
 */
class TrackingDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
