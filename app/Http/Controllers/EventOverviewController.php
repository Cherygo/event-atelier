<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventOverviewController extends Controller
{
    public function __invoke(Request $request, Event $event): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);
        $today = now()->toDateString();
        $counts = $event->tasks()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $counts->sum();
        $completed = (int) ($counts[TaskStatus::Completed->value] ?? 0);
        $active = $event->tasks()->where('status', '!=', TaskStatus::Completed);

        return Inertia::render('Events/Overview', [
            'event' => $event->only(['id', 'name', 'location', 'event_date']),
            'metrics' => [
                'total' => $total,
                'todo' => (int) ($counts[TaskStatus::Todo->value] ?? 0),
                'in_progress' => (int) ($counts[TaskStatus::InProgress->value] ?? 0),
                'completed' => $completed,
                'percent' => $total ? (int) round($completed / $total * 100) : 0,
                'overdue' => (clone $active)->where('due_date', '<', $today)->count(),
                'today' => (clone $active)->where('due_date', $today)->count(),
            ],
            'attention' => (clone $active)->with('assignee:id,name')->whereNotNull('due_date')
                ->where('due_date', '<=', now()->addDays(7)->toDateString())->orderBy('due_date')->orderBy('id')->limit(6)->get(),
            'today' => $today,
            'canEdit' => $request->user()->can('editPlanning', $event),
        ]);
    }
}
