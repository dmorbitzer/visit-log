<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
}
