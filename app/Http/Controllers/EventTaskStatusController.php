<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Event;
use App\Models\EventTask;
use App\TaskStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EventTaskStatusController extends Controller
{
    public function update(UpdateTaskStatusRequest $request, Event $event, EventTask $task): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $task): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $task = $event->tasks()->findOrFail($task->id);
            $status = TaskStatus::from($request->validated('status'));
            if ($task->status !== $status) {
                $task->status = $status;
                $task->completed_at = $status === TaskStatus::Completed ? now() : null;
                $task->save();
            }
        });

        return back()->with('status', 'Task status updated.');
    }
}
