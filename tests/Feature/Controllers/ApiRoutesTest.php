<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\TrackingDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function asAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum');

        return $admin;
    }

    private function asTracker(Event $event): User
    {
        $tracker = User::factory()->create();
        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);
        $this->actingAs($tracker, 'sanctum');

        return $tracker;
    }

    // -------------------------------------------------------------------------
    // Auth
    // -------------------------------------------------------------------------

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/events')->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // Events
    // -------------------------------------------------------------------------

    public function test_get_events_returns_json(): void
    {
        $this->asAdmin();
        Event::factory()->count(2)->create();
        Event::factory()->archived()->create();

        $response = $this->getJson('/api/events');

        $response->assertOk()
            ->assertJsonStructure(['active', 'archived'])
            ->assertJsonCount(2, 'active')
            ->assertJsonCount(1, 'archived');
    }

    public function test_post_events_creates_and_returns_201(): void
    {
        $this->asAdmin();

        $response = $this->postJson('/api/events', [
            'name' => 'API Event',
            'type' => 'recurring',
            'recurrence_weekdays' => [0],
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ]);

        $response->assertCreated()
            ->assertJsonPath('name', 'API Event');
    }

    public function test_get_event_returns_json(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();

        $this->getJson("/api/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('id', $event->id);
    }

    public function test_patch_event_updates_and_returns_json(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();

        $this->patchJson("/api/events/{$event->id}", [
            'name' => 'Updated',
            'type' => 'recurring',
            'recurrence_weekdays' => [1],
            'tracking_start' => '08:00',
            'tracking_end' => '14:00',
            'slot_interval_minutes' => 60,
        ])->assertOk()->assertJsonPath('name', 'Updated');
    }

    public function test_patch_archive_sets_status_archived(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();

        $this->patchJson("/api/events/{$event->id}/archive")
            ->assertOk()
            ->assertJsonPath('status', 'archived');
    }

    public function test_delete_event_returns_204(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();

        $this->deleteJson("/api/events/{$event->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_tracker_cannot_delete_event(): void
    {
        $event = Event::factory()->create();
        $this->asTracker($event);

        $this->deleteJson("/api/events/{$event->id}")->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Users
    // -------------------------------------------------------------------------

    public function test_get_users_returns_json(): void
    {
        $this->asAdmin();
        User::factory()->count(2)->create();

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure([['id', 'name', 'username']]);
    }

    public function test_post_users_creates_and_returns_201(): void
    {
        $this->asAdmin();

        $this->postJson('/api/users', [
            'name' => 'New User',
            'username' => 'newuser',
            'password' => 'Password1!',
            'role' => 'tracker',
        ])->assertCreated()->assertJsonPath('username', 'newuser');
    }

    public function test_patch_user_updates_and_returns_json(): void
    {
        $this->asAdmin();
        $user = User::factory()->create(['name' => 'Old']);

        $this->patchJson("/api/users/{$user->id}", [
            'name' => 'New',
            'username' => $user->username,
            'role' => 'tracker',
        ])->assertOk()->assertJsonPath('name', 'New');
    }

    public function test_delete_user_returns_204(): void
    {
        $this->asAdmin();
        $user = User::factory()->create();

        $this->deleteJson("/api/users/{$user->id}")->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_tracker_cannot_access_users_endpoint(): void
    {
        $event = Event::factory()->create();
        $this->asTracker($event);

        $this->getJson('/api/users')->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Tracking Days
    // -------------------------------------------------------------------------

    public function test_get_days_returns_json(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();
        TrackingDay::factory()->for($event)->create();

        $this->getJson("/api/events/{$event->id}/days")
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_get_day_returns_null_for_untracked_date(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();

        $this->getJson("/api/events/{$event->id}/days/2026-01-01")
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_patch_track_lazy_creates_and_returns_count(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();

        $this->patchJson("/api/events/{$event->id}/days/2026-05-14/track", [
            'slot_start' => '09:00',
            'slot_end' => '09:30',
            'action' => 'increment',
        ])->assertOk()->assertJsonPath('visitor_count', 1);
    }

    public function test_tracker_can_track_assigned_event(): void
    {
        $event = Event::factory()->create();
        $this->asTracker($event);

        $this->patchJson("/api/events/{$event->id}/days/2026-05-14/track", [
            'slot_start' => '09:00',
            'slot_end' => '09:30',
            'action' => 'increment',
        ])->assertOk();
    }

    // -------------------------------------------------------------------------
    // Event Users
    // -------------------------------------------------------------------------

    public function test_post_event_users_assigns_user(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();
        $user = User::factory()->create();

        $this->postJson("/api/events/{$event->id}/users", [
            'user_id' => $user->id,
            'permission' => 'tracker',
        ])->assertNoContent();

        $this->assertDatabaseHas('event_user', ['event_id' => $event->id, 'user_id' => $user->id]);
    }

    public function test_delete_event_user_removes_assignment(): void
    {
        $this->asAdmin();
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $event->users()->attach($user->id, ['permission' => Permission::Tracker->value]);

        $this->deleteJson("/api/events/{$event->id}/users/{$user->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('event_user', ['event_id' => $event->id, 'user_id' => $user->id]);
    }
}
