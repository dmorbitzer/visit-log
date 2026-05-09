<?php

namespace Tests\Feature\Policies;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TrackingDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSlotPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_time_slot(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();
        $slot = TimeSlot::factory()->for($day)->create();

        $this->assertTrue($admin->can('view', $slot));
    }

    public function test_assigned_tracker_can_view_time_slot(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();
        $slot = TimeSlot::factory()->for($day)->create();

        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);
        $tracker->load('events');

        $this->assertTrue($tracker->can('view', $slot));
    }

    public function test_unassigned_tracker_cannot_view_time_slot(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();
        $slot = TimeSlot::factory()->for($day)->create();

        $tracker->load('events');

        $this->assertFalse($tracker->can('view', $slot));
    }

    public function test_admin_can_track(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();
        $slot = TimeSlot::factory()->for($day)->create();

        $this->assertTrue($admin->can('track', $slot));
    }

    public function test_tracker_with_tracker_permission_can_track(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();
        $slot = TimeSlot::factory()->for($day)->create();

        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);
        $tracker->load('events');

        $this->assertTrue($tracker->can('track', $slot));
    }

    public function test_viewer_cannot_track(): void
    {
        $viewer = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();
        $slot = TimeSlot::factory()->for($day)->create();

        $event->users()->attach($viewer->id, ['permission' => Permission::Viewer->value]);
        $viewer->load('events');

        $this->assertFalse($viewer->can('track', $slot));
    }
}
