<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Events\DayNotesUpdated;
use App\Events\SlotUpdated;
use App\Models\Event;
use App\Models\TimeSlot;
use App\Models\TrackingDay;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrackingDayController extends Controller
{
    public function index(Request $request, Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $days = $event->trackingDays()
            ->with('timeSlots')
            ->when($request->from, fn ($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('date', '<=', $request->to))
            ->orderBy('date')
            ->get();

        return response()->json($days);
    }

    public function show(Event $event, string $date): JsonResponse
    {
        $this->authorize('view', $event);

        $day = $event->trackingDays()
            ->with('timeSlots')
            ->whereDate('date', $date)
            ->first();

        return response()->json($day);
    }

    public function track(Request $request, Event $event, string $date): JsonResponse
    {
        abort_if($event->status === EventStatus::Archived, 403, 'Event is archived.');

        /** @var User $user */
        $user = Auth::user();
        abort_if(! $user->isAdmin() && ! $user->canTrack($event), 403);

        $validated = $request->validate([
            'slot_start' => ['required', 'date_format:H:i'],
            'slot_end' => ['required', 'date_format:H:i', 'after:slot_start'],
            'action' => ['required', Rule::in(['increment', 'decrement', 'set'])],
            'value' => ['integer', 'min:0', 'required_if:action,set'],
        ]);

        try {
            $day = TrackingDay::firstOrCreate([
                'event_id' => $event->id,
                'date' => $date,
            ]);
        } catch (UniqueConstraintViolationException) {
            $day = TrackingDay::where('event_id', $event->id)
                ->whereDate('date', $date)
                ->firstOrFail();
        }

        try {
            $slot = TimeSlot::firstOrCreate(
                ['tracking_day_id' => $day->id, 'slot_start' => $validated['slot_start']],
                ['slot_end' => $validated['slot_end'], 'visitor_count' => 0],
            );
        } catch (UniqueConstraintViolationException) {
            $slot = TimeSlot::where('tracking_day_id', $day->id)
                ->where('slot_start', $validated['slot_start'])
                ->firstOrFail();
        }

        match ($validated['action']) {
            'increment' => $slot->increment('visitor_count'),
            'decrement' => TimeSlot::where('id', $slot->id)
                ->where('visitor_count', '>', 0)
                ->decrement('visitor_count'),
            'set' => $slot->update(['visitor_count' => max(0, $validated['value'])]),
        };

        $slot->refresh();

        try {
            broadcast(new SlotUpdated($slot));
        } catch (\Throwable) {
            // Reverb nicht erreichbar — Tracking trotzdem erfolgreich
        }

        return response()->json([
            'id' => $slot->id,
            'visitor_count' => $slot->visitor_count,
        ]);
    }

    public function update(Request $request, Event $event, string $date): JsonResponse
    {
        abort_if($event->status === EventStatus::Archived, 403, 'Event is archived.');

        $day = $event->trackingDays()->whereDate('date', $date)->firstOrFail();

        $this->authorize('updateNotes', $day);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $day->update($validated);

        try {
            broadcast(new DayNotesUpdated($day));
        } catch (\Throwable) {
            // Reverb nicht erreichbar
        }

        return response()->json(['notes' => $day->notes]);
    }
}
