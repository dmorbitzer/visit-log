<?php

namespace Tests\Feature;

use App\Enums\EventStatus;
use App\Enums\Permission;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_index(): void
    {
        $this->get(route('events.index'))->assertRedirect(route('login'));
    }

    public function test_admin_sees_all_events_on_index(): void
    {
        $admin = User::factory()->admin()->create();
        Event::factory()->count(2)->create();
        Event::factory()->archived()->create();

        $response = $this->actingAs($admin)->get(route('events.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('events/index')
            ->has('activeEvents', 2)
            ->has('archivedEvents', 1)
            ->where('canManage', true)
        );
    }

    public function test_tracker_only_sees_assigned_events_on_index(): void
    {
        $tracker = User::factory()->create();
        $assigned = Event::factory()->create();
        Event::factory()->create();

        $assigned->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);

        $response = $this->actingAs($tracker)->get(route('events.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('activeEvents', 1)
            ->where('canManage', false)
        );
    }

    public function test_guest_is_redirected_from_create(): void
    {
        $this->get(route('events.create'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_create_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('events.create'))->assertOk();
    }

    public function test_tracker_cannot_access_create_page(): void
    {
        $tracker = User::factory()->create();

        $this->actingAs($tracker)->get(route('events.create'))->assertForbidden();
    }

    public function test_admin_can_create_a_recurring_event(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('events.store'), [
            'name' => 'Sunday Service',
            'type' => 'recurring',
            'recurrence_weekdays' => [0],
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', ['name' => 'Sunday Service', 'status' => EventStatus::Active->value]);
    }

    public function test_admin_can_create_a_date_range_event(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('events.store'), [
            'name' => 'Conference',
            'type' => 'date_range',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'tracking_start' => '08:00',
            'tracking_end' => '18:00',
            'slot_interval_minutes' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', ['name' => 'Conference']);
    }

    public function test_tracker_cannot_create_event(): void
    {
        $tracker = User::factory()->create();

        $this->actingAs($tracker)->post(route('events.store'), [
            'name' => 'Test',
            'type' => 'recurring',
            'recurrence_weekdays' => [0],
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ])->assertForbidden();
    }

    public function test_store_requires_name(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('events.store'), [
            'type' => 'recurring',
            'recurrence_weekdays' => [0],
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ])->assertSessionHasErrors('name');
    }

    public function test_store_requires_recurrence_weekdays_for_recurring_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('events.store'), [
            'name' => 'Test',
            'type' => 'recurring',
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ])->assertSessionHasErrors('recurrence_weekdays');
    }

    public function test_store_requires_dates_for_date_range_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('events.store'), [
            'name' => 'Test',
            'type' => 'date_range',
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ])->assertSessionHasErrors(['start_date', 'end_date']);
    }

    public function test_store_requires_tracking_end_after_tracking_start(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('events.store'), [
            'name' => 'Test',
            'type' => 'recurring',
            'recurrence_weekdays' => [0],
            'tracking_start' => '12:00',
            'tracking_end' => '09:00',
            'slot_interval_minutes' => 30,
        ])->assertSessionHasErrors('tracking_end');
    }

    public function test_guest_is_redirected_from_show(): void
    {
        $event = Event::factory()->create();

        $this->get(route('events.show', $event))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_any_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->get(route('events.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('events/show')
                ->where('canManage', true)
            );
    }

    public function test_tracker_can_view_assigned_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);

        $this->actingAs($tracker)->get(route('events.show', $event))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('canManage', false));
    }

    public function test_tracker_cannot_view_unassigned_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)->get(route('events.show', $event))->assertForbidden();
    }

    public function test_admin_can_access_edit_page(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->get(route('events.edit', $event))->assertOk();
    }

    public function test_tracker_cannot_access_edit_page(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)->get(route('events.edit', $event))->assertForbidden();
    }

    public function test_admin_can_update_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->patch(route('events.update', $event), [
            'name' => 'Updated Name',
            'type' => 'recurring',
            'recurrence_weekdays' => [1, 3],
            'tracking_start' => '08:00',
            'tracking_end' => '14:00',
            'slot_interval_minutes' => 60,
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Updated Name']);
    }

    public function test_tracker_cannot_update_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)->patch(route('events.update', $event), [
            'name' => 'Hacked',
            'type' => 'recurring',
            'recurrence_weekdays' => [0],
            'tracking_start' => '09:00',
            'tracking_end' => '12:00',
            'slot_interval_minutes' => 30,
        ])->assertForbidden();
    }

    public function test_admin_can_archive_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->patch(route('events.archive', $event))
            ->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => EventStatus::Archived->value]);
    }

    public function test_tracker_cannot_archive_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)->patch(route('events.archive', $event))->assertForbidden();
    }

    public function test_admin_can_delete_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->delete(route('events.destroy', $event))
            ->assertRedirect(route('events.index'));

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_tracker_cannot_delete_event(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)->delete(route('events.destroy', $event))->assertForbidden();
    }
}
