<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Events/Index', [
            'events' => Event::query()
                ->where(fn ($query) => $query->where('user_id', $request->user()->id)
                    ->orWhereHas('members', fn ($members) => $members->where('user_id', $request->user()->id)))
                ->latest()
                ->get(['id', 'name', 'type', 'event_date', 'guest_count', 'location', 'created_at']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Events/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request): RedirectResponse
    {
        $request->user()->events()->create($request->validated());

        return to_route('events.index');
    }

    public function show(Request $request, Event $event): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);

        return Inertia::render('Events/Show', [
            'event' => $event->only(['id', 'name', 'type', 'event_date', 'guest_count', 'location']),
            'role' => $event->roleFor($request->user())->label(),
            'can' => [
                'update' => $request->user()->can('update', $event),
                'editPlanning' => $request->user()->can('editPlanning', $event),
                'delete' => $request->user()->can('delete', $event),
            ],
        ]);
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('update', $event);
            $event->update($request->validated());
        });

        return back()->with('status', 'Event details saved.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        Gate::authorize('view', $event);
        Gate::authorize('delete', $event);
        $request->validate(['password' => ['required', 'current_password']]);
        DB::transaction(function () use ($event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('delete', $event);
            $event->delete();
        });

        return to_route('events.index')->with('status', 'Event deleted.');
    }
}
