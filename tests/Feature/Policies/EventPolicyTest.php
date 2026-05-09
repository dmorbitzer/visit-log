<?php

namespace Tests\Feature\Policies;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_event(): void
    {
        $admin = User::factory()->admin()->create();
        $this->assertTrue($admin->can('create', Event::class));
    }

    public function test_tracker_cannot_create_event(): void
    {
        $tracker = User::factory()->create();
        $this->assertFalse($tracker->can('create', Event::class));
    }

    public function test_admin_can_update_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $this->assertTrue($admin->can('update', $event));
    }

    public function test_tracker_cannot_update_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $this->assertFalse($tracker->can('update', $event));
    }

    public function test_admin_can_delete_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $this->assertTrue($admin->can('delete', $event));
    }

    public function test_admin_can_archive_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $this->assertTrue($admin->can('archive', $event));
    }

    public function test_assigned_tracker_can_view_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);
        $tracker->load('events');
        $this->assertTrue($tracker->can('view', $event));
    }

    public function test_unassigned_tracker_cannot_view_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $tracker->load('events');
        $this->assertFalse($tracker->can('view', $event));
    }
}
