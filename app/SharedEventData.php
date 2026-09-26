<?php

namespace App;

use App\Models\Event;
use App\Models\EventShare;

class SharedEventData
{
    public function __construct(private EventBudgetSummary $budgetSummary) {}

    /** @return array<string, mixed> */
    public function forEvent(Event $event, EventShare $share): array
    {
        $data = [
            'name' => $event->name,
            'date' => $share->show_date ? $event->event_date?->toDateString() : null,
            'location' => $share->show_location ? $event->location : null,
            'note' => $share->public_note,
        ];

        if ($share->show_progress) {
            $counts = $event->tasks()->selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status');
            $data['progress'] = ['total' => (int) $counts->sum(), 'completed' => (int) ($counts[TaskStatus::Completed->value] ?? 0)];
        }
        if ($share->show_vendors) {
            $booked = $event->vendors()->where('status', VendorStatus::Booked);
            $data['vendors'] = [
                'total' => $booked->count(),
                'items' => $booked->orderBy('name')->orderBy('id')->limit(50)->get(['name', 'category'])->toArray(),
            ];
        }
        if ($share->show_budget) {
            $data['budget'] = $event->budget_currency === null ? null : [
                'currency' => $event->budget_currency,
                'target_minor' => $event->budget_target_minor,
                'forecast_minor' => $this->budgetSummary->forEvent($event)['totals']['forecast_minor'],
            ];
        }

        return $data;
    }
}
