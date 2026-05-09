<?php

namespace Tests\Feature\Policies;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\TrackingDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingDayPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_tracking_day(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();

        $this->assertTrue($admin->can('view', $day));
    }

    public function test_assigned_tracker_can_view_tracking_day(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();

        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);
        $tracker->load('events');

        $this->assertTrue($tracker->can('view', $day));
    }

    public function test_unassigned_tracker_cannot_view_tracking_day(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();

        $tracker->load('events');

        $this->assertFalse($tracker->can('view', $day));
    }

    public function test_admin_can_update_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();

        $this->assertTrue($admin->can('updateNotes', $day));
    }

    public function test_tracker_with_tracker_permission_can_update_notes(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();

        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);
        $tracker->load('events');

        $this->assertTrue($tracker->can('updateNotes', $day));
    }

    public function test_tracker_with_viewer_permission_cannot_update_notes(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create();

        $event->users()->attach($tracker->id, ['permission' => Permission::Viewer->value]);
        $tracker->load('events');

        $this->assertFalse($tracker->can('updateNotes', $day));
    }
}
