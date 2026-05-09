<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TrackingDay;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $trackers = User::factory(3)->create();

        $event = Event::factory()->recurring()->create([
            'name' => 'Test Event',
        ]);

        TrackingDay::factory(4)
            ->for($event)
            ->create()
            ->each(function ($day) {
                TimeSlot::factory(8)->for($day)->create();
            });

        $event->users()->attach(
            $trackers->pluck('id'),
            ['permission' => Permission::Tracker->value]
        );

        $fair = Event::factory()->dateRange()->archived()->create([
            'name' => 'Test Fair',
        ]);

        TrackingDay::factory(3)
            ->for($fair)
            ->create()
            ->each(function ($day) {
                TimeSlot::factory(8)->for($day)->create();
            });
    }
}
