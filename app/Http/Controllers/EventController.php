<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('events/index', [
            'activeEvents' => Event::active()->forUser($user)->get(),
            'archivedEvents' => Event::archived()->forUser($user)->get(),
            'canManage' => $user->isAdmin(),
        ]);
    }

    public function show(Event $event): Response
    {
        $this->authorize('view', $event);

        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('events/show', [
            'event' => $event,
            'canManage' => $user->isAdmin(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('events/create');
    }

    public function edit(Event $event): Response
    {
        $this->authorize('update', $event);

        return Inertia::render('events/edit', [
            'event' => $event,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate($this->validationRules());

        $event = Event::create([
            ...$validated,
            'status' => 'active',
        ]);

        return redirect()->route('events.show', $event);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->update($request->validate($this->validationRules()));

        return redirect()->route('events.show', $event);
    }

    public function archive(Event $event): RedirectResponse
    {
        $this->authorize('archive', $event);

        $event->update(['status' => EventStatus::Archived]);

        return redirect()->route('events.index');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

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
