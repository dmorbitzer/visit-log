<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('events/index', [
            'activeEvents' => Event::active()->forUser(auth()->user())->get(),
            'archivedEvents' => Event::archived()->forUser(auth()->user())->get(),
        ]);
    }

    public function show(Event $event): Response
    {
        $this->authorize('view', $event);

        return Inertia::render('events/show', [
            'event' => $event,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Event::class);

        return Inertia::render('events/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:recurring,date_range,custom_days'],
            'recurrence_weekdays' => ['nullable', 'array', 'required_if:type,recurring'],
            'recurrence_weekdays.*' => ['integer', 'between:0,6'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'tracking_start' => ['required', 'date_format:H:i'],
            'tracking_end' => ['required', 'date_format:H:i', 'after:tracking_start'],
            'slot_interval_minutes' => ['required', 'integer', 'in:15,30,60'],
        ]);

        $event = Event::create([
            ...$validated,
            'status' => 'active',
        ]);

        return redirect()->route('events.show', $event);
    }
}
