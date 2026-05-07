<?php

namespace Tests\Feature\Actions;

use App\Actions\EnsureSlotsExist;
use App\Actions\GenerateSlots;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TrackingDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureSlotsExistTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_create_slots_if_already_exist(): void
    {
        $event = Event::factory()->create([
            'tracking_start' => '12:00',
            'tracking_end' => '13:00',
            'slot_interval_minutes' => 30,
        ]);

        $day = TrackingDay::factory()->for($event)->create();

        // Slots bereits vorhanden
        TimeSlot::factory(2)->for($day)->create();

        $countBefore = TimeSlot::where('tracking_day_id', $day->id)->count();

        (new EnsureSlotsExist(new GenerateSlots))->ensure($day);

        $this->assertEquals($countBefore, TimeSlot::where('tracking_day_id', $day->id)->count());
    }

    public function test_creates_slots_if_none_exist(): void
    {
        $event = Event::factory()->create([
            'tracking_start' => '12:00',
            'tracking_end' => '13:00',
            'slot_interval_minutes' => 30,
        ]);

        $day = TrackingDay::factory()->for($event)->create();

        (new EnsureSlotsExist(new GenerateSlots))->ensure($day);

        $this->assertEquals(2, TimeSlot::where('tracking_day_id', $day->id)->count());
    }
}
