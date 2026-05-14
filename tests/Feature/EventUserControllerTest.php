<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventUserControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // index (JSON)
    // -------------------------------------------------------------------------

    public function test_admin_can_fetch_event_users_as_json(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $tracker = User::factory()->create();
        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);

        $response = $this->actingAs($admin)->getJson(route('events.users.index', $event));

        $response->assertOk()->assertJsonCount(1)->assertJsonFragment([
            'id' => $tracker->id,
            'permission' => Permission::Tracker->value,
        ]);
    }

    public function test_tracker_cannot_fetch_event_users(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)
            ->getJson(route('events.users.index', $event))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_admin_can_assign_user_to_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('events.users.store', $event), [
            'user_id' => $user->id,
            'permission' => Permission::Tracker->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'permission' => Permission::Tracker->value,
        ]);
    }

    public function test_assigning_same_user_twice_updates_permission(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $event->users()->attach($user->id, ['permission' => Permission::Viewer->value]);

        $this->actingAs($admin)->post(route('events.users.store', $event), [
            'user_id' => $user->id,
            'permission' => Permission::Tracker->value,
        ]);

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'permission' => Permission::Tracker->value,
        ]);
        $this->assertDatabaseCount('event_user', 1);
    }

    public function test_tracker_cannot_assign_user_to_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($tracker)->post(route('events.users.store', $event), [
            'user_id' => $other->id,
            'permission' => Permission::Tracker->value,
        ])->assertForbidden();
    }

    public function test_store_requires_valid_user_id(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->post(route('events.users.store', $event), [
            'user_id' => 99999,
            'permission' => Permission::Tracker->value,
        ])->assertSessionHasErrors('user_id');
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_admin_can_update_user_permission(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $event->users()->attach($user->id, ['permission' => Permission::Tracker->value]);

        $this->actingAs($admin)->patch(
            route('events.users.update', [$event, $user]),
            ['permission' => Permission::Viewer->value]
        )->assertRedirect();

        $this->assertDatabaseHas('event_user', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'permission' => Permission::Viewer->value,
        ]);
    }

    public function test_tracker_cannot_update_user_permission(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $other = User::factory()->create();
        $event->users()->attach($other->id, ['permission' => Permission::Tracker->value]);

        $this->actingAs($tracker)->patch(
            route('events.users.update', [$event, $other]),
            ['permission' => Permission::Viewer->value]
        )->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_admin_can_remove_user_from_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $event->users()->attach($user->id, ['permission' => Permission::Tracker->value]);

        $this->actingAs($admin)
            ->delete(route('events.users.destroy', [$event, $user]))
            ->assertRedirect();

        $this->assertDatabaseMissing('event_user', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_tracker_cannot_remove_user_from_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $other = User::factory()->create();
        $event->users()->attach($other->id, ['permission' => Permission::Tracker->value]);

        $this->actingAs($tracker)
            ->delete(route('events.users.destroy', [$event, $other]))
            ->assertForbidden();
    }
}
