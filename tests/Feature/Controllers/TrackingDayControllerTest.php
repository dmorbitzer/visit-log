<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permission;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TrackingDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingDayControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $today;

    protected function setUp(): void
    {
        parent::setUp();
        $this->today = now()->toDateString();
    }

    // -------------------------------------------------------------------------
    // index
    // -------------------------------------------------------------------------

    public function test_guest_cannot_access_index(): void
    {
        $event = Event::factory()->create();

        $this->getJson(route('events.days.index', $event))->assertUnauthorized();
    }

    public function test_admin_can_list_tracking_days(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        TrackingDay::factory()->for($event)->create(['date' => $this->today]);

        $this->actingAs($admin)
            ->getJson(route('events.days.index', $event))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_index_filters_by_from_and_to(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        TrackingDay::factory()->for($event)->create(['date' => '2026-01-10']);
        TrackingDay::factory()->for($event)->create(['date' => '2026-01-20']);
        TrackingDay::factory()->for($event)->create(['date' => '2026-02-01']);

        $response = $this->actingAs($admin)
            ->getJson(route('events.days.index', $event).'?from=2026-01-01&to=2026-01-31');

        $response->assertOk()->assertJsonCount(2);
    }

    // -------------------------------------------------------------------------
    // show
    // -------------------------------------------------------------------------

    public function test_show_returns_existing_day_with_slots(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create(['date' => $this->today]);
        TimeSlot::factory()->for($day)->create(['slot_start' => '09:00', 'visitor_count' => 5]);

        $response = $this->actingAs($admin)
            ->getJson(route('events.days.show', [$event, $this->today]));

        $response->assertOk()
            ->assertJsonCount(1, 'time_slots');
    }

    public function test_show_returns_null_for_non_existing_day(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('events.days.show', [$event, $this->today]))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_show_does_not_create_day(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)
            ->getJson(route('events.days.show', [$event, $this->today]));

        $this->assertFalse(
            TrackingDay::where('event_id', $event->id)->whereDate('date', $this->today)->exists()
        );
    }

    public function test_tracker_without_permission_cannot_view_day(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)
            ->getJson(route('events.days.show', [$event, $this->today]))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // track — lazy creation
    // -------------------------------------------------------------------------

    public function test_track_creates_day_and_slot_on_first_track(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->assertDatabaseMissing('tracking_days', ['event_id' => $event->id]);

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertOk();

        $this->assertTrue(
            TrackingDay::where('event_id', $event->id)->whereDate('date', $this->today)->exists()
        );
        $this->assertDatabaseHas('time_slots', ['slot_start' => '09:00', 'visitor_count' => 1]);
    }

    public function test_get_show_before_track_does_not_pre_create_day(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        // First fetch the day (should NOT create it)
        $this->actingAs($admin)
            ->getJson(route('events.days.show', [$event, $this->today]))
            ->assertOk();

        $this->assertFalse(
            TrackingDay::where('event_id', $event->id)->whereDate('date', $this->today)->exists()
        );

        // Now track — this should create the day
        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertOk();

        $this->assertTrue(
            TrackingDay::where('event_id', $event->id)->whereDate('date', $this->today)->exists()
        );
    }

    public function test_track_creates_missing_slot_after_config_change(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create(['date' => $this->today]);
        // Existing slot from old config
        TimeSlot::factory()->for($day)->create(['slot_start' => '08:00', 'slot_end' => '08:30']);

        // Track a new slot (config changed, new slot doesn't exist yet)
        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '10:00', 'slot_end' => '10:30', 'action' => 'increment']
        )->assertOk();

        $this->assertDatabaseHas('time_slots', ['slot_start' => '10:00', 'visitor_count' => 1]);
        $this->assertDatabaseHas('time_slots', ['slot_start' => '08:00']); // old slot untouched
    }

    // -------------------------------------------------------------------------
    // track — actions
    // -------------------------------------------------------------------------

    public function test_track_increment_increases_visitor_count(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertOk()->assertJsonPath('visitor_count', 1);

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertJsonPath('visitor_count', 2);
    }

    public function test_track_decrement_decreases_visitor_count(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create(['date' => $this->today]);
        TimeSlot::factory()->for($day)->create(['slot_start' => '09:00', 'slot_end' => '09:30', 'visitor_count' => 3]);

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'decrement']
        )->assertOk()->assertJsonPath('visitor_count', 2);
    }

    public function test_track_decrement_does_not_go_below_zero(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create(['date' => $this->today]);
        TimeSlot::factory()->for($day)->create(['slot_start' => '09:00', 'slot_end' => '09:30', 'visitor_count' => 0]);

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'decrement']
        )->assertOk()->assertJsonPath('visitor_count', 0);

        $this->assertDatabaseHas('time_slots', ['slot_start' => '09:00', 'visitor_count' => 0]);
    }

    public function test_track_set_updates_to_absolute_value(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'set', 'value' => 12]
        )->assertOk()->assertJsonPath('visitor_count', 12);
    }

    public function test_track_set_rejects_negative_value(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'set', 'value' => -5]
        )->assertUnprocessable();
    }

    // -------------------------------------------------------------------------
    // track — authorization
    // -------------------------------------------------------------------------

    public function test_track_returns_403_for_archived_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->archived()->create();

        $this->actingAs($admin)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertForbidden();
    }

    public function test_assigned_tracker_can_track(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();
        $event->users()->attach($tracker->id, ['permission' => Permission::Tracker->value]);

        $this->actingAs($tracker)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertOk();
    }

    public function test_unassigned_tracker_cannot_track(): void
    {
        $tracker = User::factory()->create();
        $event = Event::factory()->create();

        $this->actingAs($tracker)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertForbidden();
    }

    public function test_viewer_cannot_track(): void
    {
        $viewer = User::factory()->create();
        $event = Event::factory()->create();
        $event->users()->attach($viewer->id, ['permission' => Permission::Viewer->value]);

        $this->actingAs($viewer)->patchJson(
            route('events.days.track', [$event, $this->today]),
            ['slot_start' => '09:00', 'slot_end' => '09:30', 'action' => 'increment']
        )->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // update (notes)
    // -------------------------------------------------------------------------

    public function test_admin_can_update_notes(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $day = TrackingDay::factory()->for($event)->create(['date' => $this->today]);

        $this->actingAs($admin)->patchJson(
            route('events.days.update', [$event, $this->today]),
            ['notes' => 'Very busy day']
        )->assertOk()->assertJsonPath('notes', 'Very busy day');

        $this->assertDatabaseHas('tracking_days', ['id' => $day->id, 'notes' => 'Very busy day']);
    }

    public function test_update_notes_returns_403_for_archived_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->archived()->create();
        TrackingDay::factory()->for($event)->create(['date' => $this->today]);

        $this->actingAs($admin)->patchJson(
            route('events.days.update', [$event, $this->today]),
            ['notes' => 'Should not save']
        )->assertForbidden();
    }
}
