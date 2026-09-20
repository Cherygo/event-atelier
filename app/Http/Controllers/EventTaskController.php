<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEventTaskRequest;
use App\Models\Event;
use App\Models\EventTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventTaskController extends Controller
{
    public function index(Request $request, Event $event): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'page' => ['nullable', 'integer', 'min:1']]);
        $tasks = $event->tasks();
        if (! empty($filters['search'])) {
            $tasks->where('title', 'like', '%'.$filters['search'].'%');
        }

        return Inertia::render('Events/Tasks', [
            'event' => $event->only(['id', 'name']),
            'tasks' => $tasks->latest('id')->paginate(20)->withQueryString(),
            'filters' => ['search' => $filters['search'] ?? ''],
            'canEdit' => $request->user()->can('editPlanning', $event),
        ]);
    }

    public function store(SaveEventTaskRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->tasks()->create($request->validated());
        });

        return back()->with('status', 'Task added.');
    }

    public function update(SaveEventTaskRequest $request, Event $event, EventTask $task): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $task): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->tasks()->findOrFail($task->id)->update($request->validated());
        });

        return back()->with('status', 'Task saved.');
    }

    public function destroy(Event $event, EventTask $task): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $task): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->tasks()->findOrFail($task->id)->delete();
        });

        return back()->with('status', 'Task deleted.');
    }
}
