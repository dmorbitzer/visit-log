<?php

namespace Tests\Feature\Event;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_track_any_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->assertTrue($admin->canTrack($event));
    }

    public function test_tracker_with_tracker_permission_can_track(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $event->users()->attach($tracker->id, [
            'permission' => Permission::Tracker->value,
        ]);

        $tracker->load('events');

        $this->assertTrue($tracker->canTrack($event));
    }

    public function test_tracker_with_viewer_permission_cannot_track(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $event->users()->attach($tracker->id, [
            'permission' => Permission::Viewer->value,
        ]);

        $tracker->load('events');

        $this->assertFalse($tracker->canTrack($event));
    }

    public function test_viewer_can_view_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $event->users()->attach($user->id, [
            'permission' => Permission::Viewer->value,
        ]);

        $user->load('events');

        $this->assertTrue($user->canView($event));
    }

    public function test_unassigned_user_cannot_view_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $user->load('events');

        $this->assertFalse($user->canView($event));
    }
}
