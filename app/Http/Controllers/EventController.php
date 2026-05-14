<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * @group Events
 */
class EventController extends Controller
{
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $activeEvents = Event::active()->forUser($user)->get();
        $archivedEvents = Event::archived()->forUser($user)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'active' => $activeEvents,
                'archived' => $archivedEvents,
            ]);
        }

        return Inertia::render('events/index', [
            'activeEvents' => $activeEvents,
            'archivedEvents' => $archivedEvents,
            'canManage' => $user->isAdmin(),
            'allUsers' => $user->isAdmin() ? User::orderBy('name')->get(['id', 'name', 'username']) : [],
        ]);
    }

    public function show(Request $request, Event $event): InertiaResponse|JsonResponse
    {
        $this->authorize('view', $event);

        if ($request->wantsJson()) {
            return response()->json($event);
        }

        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('events/show', [
            'event' => $event,
            'canManage' => $user->isAdmin(),
            'allUsers' => $user->isAdmin() ? User::orderBy('name')->get(['id', 'name', 'username']) : [],
        ]);
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Event::class);

        return Inertia::render('events/create');
    }

    public function edit(Event $event): InertiaResponse
    {
        $this->authorize('update', $event);

        return Inertia::render('events/edit', [
            'event' => $event,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            ...$request->validate($this->validationRules()),
            'status' => 'active',
        ]);

        if ($request->wantsJson()) {
            return response()->json($event, 201);
        }

        return redirect()->route('events.show', $event);
    }

    public function update(Request $request, Event $event): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $event);

        $event->update($request->validate($this->validationRules()));

        if ($request->wantsJson()) {
            return response()->json($event->fresh());
        }

        return redirect()->route('events.show', $event);
    }

    public function archive(Request $request, Event $event): RedirectResponse|JsonResponse
    {
        $this->authorize('archive', $event);

        $event->update(['status' => EventStatus::Archived]);

        if ($request->wantsJson()) {
            return response()->json($event->fresh());
        }

        return redirect()->route('events.index');
    }

    public function destroy(Request $request, Event $event): RedirectResponse|Response
    {
        $this->authorize('delete', $event);

        $event->delete();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->route('events.index');
    }

    private function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:recurring,date_range,custom_days'],
            'recurrence_weekdays' => ['nullable', 'array', 'required_if:type,recurring'],
            'recurrence_weekdays.*' => ['integer', 'between:0,6'],
            'start_date' => ['nullable', 'date', 'required_if:type,date_range'],
            'end_date' => ['nullable', 'date', 'required_if:type,date_range', 'after_or_equal:start_date'],
            'tracking_start' => ['required', 'date_format:H:i'],
            'tracking_end' => ['required', 'date_format:H:i', 'after:tracking_start'],
            'slot_interval_minutes' => ['required', 'integer', 'in:15,30,60'],
        ];
    }
}
