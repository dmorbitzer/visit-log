<?php

namespace Tests\Feature\Actions;

use App\Actions\GenerateSlots;
use App\Models\Event;
use App\Models\TrackingDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateSlotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_correct_number_of_slots(): void
    {
        $event = Event::factory()->create([
            'tracking_start' => '12:00',
            'tracking_end' => '16:00',
            'slot_interval_minutes' => 30,
        ]);

        $day = TrackingDay::factory()->for($event)->create();

        $slots = (new GenerateSlots)->generate($event, $day);

        $this->assertCount(8, $slots);
    }

    public function test_slot_times_are_correct(): void
    {
        $event = Event::factory()->create([
            'tracking_start' => '12:00',
            'tracking_end' => '13:00',
            'slot_interval_minutes' => 30,
        ]);

        $day = TrackingDay::factory()->for($event)->create();

        $slots = (new GenerateSlots)->generate($event, $day);

        $this->assertEquals('12:00', $slots[0]['slot_start']);
        $this->assertEquals('12:30', $slots[0]['slot_end']);
        $this->assertEquals('12:30', $slots[1]['slot_start']);
        $this->assertEquals('13:00', $slots[1]['slot_end']);
    }

    public function test_tracking_day_id_is_set(): void
    {
        $event = Event::factory()->create([
            'tracking_start' => '12:00',
            'tracking_end' => '12:30',
            'slot_interval_minutes' => 30,
        ]);

        $day = TrackingDay::factory()->for($event)->create();

        $slots = (new GenerateSlots)->generate($event, $day);

        $this->assertEquals($day->id, $slots[0]['tracking_day_id']);
    }
}
