<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEventTaskRequest;
use App\Models\Event;
use App\Models\EventTask;
use App\TaskStatus;
use App\TaskTemplateCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventTaskController extends Controller
{
    public function index(Request $request, Event $event, TaskTemplateCatalog $catalog): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:all,active,todo,in_progress,completed'],
            'due' => ['nullable', 'in:all,overdue,today,upcoming,unscheduled'],
            'sort' => ['nullable', 'in:newest,due'],
        ]);
        $tasks = $event->tasks()->with('assignee:id,name');
        $status = $filters['status'] ?? 'active';
        if ($status === 'active') {
            $tasks->where('status', '!=', TaskStatus::Completed);
        } elseif ($status !== 'all') {
            $tasks->where('status', $status);
        }
        if (! empty($filters['search'])) {
            $tasks->where('title', 'like', '%'.$filters['search'].'%');
        }
        $today = now()->toDateString();
        $due = $filters['due'] ?? 'all';
        if ($due === 'overdue') {
            $tasks->where('due_date', '<', $today)->where('status', '!=', TaskStatus::Completed);
        } elseif ($due === 'today') {
            $tasks->where('due_date', $today);
        } elseif ($due === 'upcoming') {
            $tasks->where('due_date', '>', $today)->where('due_date', '<=', now()->addDays(7)->toDateString());
        } elseif ($due === 'unscheduled') {
            $tasks->whereNull('due_date');
        }
        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'due') {
            $tasks->orderByRaw('due_date IS NULL')->orderBy('due_date');
        }

        return Inertia::render('Events/Tasks', [
            'event' => [...$event->only(['id', 'name', 'type']), 'event_date' => $event->event_date?->toDateString()],
            'templates' => $request->user()->can('editPlanning', $event) ? $catalog->withSuggestedDates($event->event_date, today()) : [],
            'templateKeys' => $request->user()->can('editPlanning', $event) ? $event->tasks()->whereNotNull('template_key')->pluck('template_key') : [],
            'tasks' => $tasks->latest('id')->paginate(20)->withQueryString(),
            'filters' => ['search' => $filters['search'] ?? '', 'status' => $status, 'due' => $due, 'sort' => $sort],
            'today' => $today,
            'assignees' => $event->assignableUsers()->orderBy('name')->get(['id', 'name']),
            'canEdit' => $request->user()->can('editPlanning', $event),
        ]);
    }

    public function store(SaveEventTaskRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->tasks()->create($request->forEvent($event));
        });

        return back()->with('status', 'Task added.');
    }

    public function update(SaveEventTaskRequest $request, Event $event, EventTask $task): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $task): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->tasks()->findOrFail($task->id)->update($request->forEvent($event));
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
