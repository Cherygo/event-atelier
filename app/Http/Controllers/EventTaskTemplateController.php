<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportTaskTemplateRequest;
use App\Models\Event;
use App\TaskTemplateCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EventTaskTemplateController extends Controller
{
    public function store(ImportTaskTemplateRequest $request, Event $event, TaskTemplateCatalog $catalog): RedirectResponse
    {
        $data = $request->validated();
        $added = DB::transaction(function () use ($event, $data, $catalog): int {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $existing = $event->tasks()->whereNotNull('template_key')->pluck('template_key')->all();
            $added = 0;
            foreach ($catalog->find($data['template'])['items'] as $item) {
                if (! in_array($item['key'], $data['items'], true) || in_array($item['key'], $existing, true)) {
                    continue;
                }
                $event->tasks()->make([
                    'title' => $item['title'], 'category' => $item['category'], 'notes' => $item['notes'],
                ])->forceFill(['template_key' => $item['key']])->save();
                $added++;
            }

            return $added;
        });

        $message = $added === 1 ? '1 task added.' : $added.' tasks added.';

        return to_route('events.tasks.index', ['event' => $event, 'status' => 'all'])
            ->with('status', $message.' Already-added template tasks were skipped. Your existing tasks were not changed.');
    }
}
