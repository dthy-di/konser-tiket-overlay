<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::published()
            ->where('starts_at', '>=', now()->startOfDay())
            ->with('ticketTypes')
            ->orderBy('starts_at')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        abort_unless($event->status === Event::PUBLISHED, 404);

        $event->load('ticketTypes');

        return view('events.show', compact('event'));
    }
}