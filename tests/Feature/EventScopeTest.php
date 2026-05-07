<?php

namespace Tests\Feature\Event;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_events(): void
    {
        $admin = User::factory()->admin()->create();
        Event::factory(3)->create();

        $events = Event::forUser($admin)->get();

        $this->assertCount(3, $events);
    }

    public function test_tracker_only_sees_assigned_events(): void
    {
        $tracker = User::factory()->create();
        $assignedEvent = Event::factory()->create();
        Event::factory(2)->create(); // not assigned events

        $assignedEvent->users()->attach($tracker->id, [
            'permission' => Permission::Tracker->value,
        ]);

        $events = Event::forUser($tracker)->get();

        $this->assertCount(1, $events);
        $this->assertEquals($assignedEvent->id, $events->first()->id);
    }
}
