<?php

namespace App;

use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventExpensePayment;

class EventBudgetSummary
{
    /** @return array{totals: array<string, int|null>, categories: list<array<string, int|string>>} */
    public function forEvent(Event $event): array
    {
        $payments = EventExpensePayment::query()
            ->whereIn('event_expense_id', $event->expenses()->select('id'))
            ->selectRaw('event_expense_id, SUM(amount_minor) AS paid_minor')->groupBy('event_expense_id');
        $fields = ['expense_count', 'estimated_minor', 'actual_minor', 'forecast_minor', 'paid_minor', 'unconfirmed_count', 'overdue_count'];
        $categories = $event->expenses()
            ->leftJoinSub($payments, 'recorded', 'recorded.event_expense_id', '=', 'event_expenses.id')
            ->select('category')
            ->selectRaw('COUNT(*) AS expense_count, SUM(estimated_minor) AS estimated_minor,
                SUM(COALESCE(actual_minor, 0)) AS actual_minor,
                SUM(COALESCE(actual_minor, estimated_minor)) AS forecast_minor,
                SUM(COALESCE(recorded.paid_minor, 0)) AS paid_minor,
                SUM(CASE WHEN actual_minor IS NULL THEN 1 ELSE 0 END) AS unconfirmed_count,
                SUM(CASE WHEN due_date < ? AND actual_minor > COALESCE(recorded.paid_minor, 0) THEN 1 ELSE 0 END) AS overdue_count', [today()->toDateString()])
            ->groupBy('category')->orderBy('category')->get()
            ->map(function (EventExpense $category) use ($fields): array {
                $data = ['category' => $category->category];
                foreach ($fields as $field) {
                    $data[$field] = (int) $category->getAttribute($field);
                }
                $data['outstanding_minor'] = $data['actual_minor'] - $data['paid_minor'];

                return $data;
            });

        $totals = [];
        foreach ([...$fields, 'outstanding_minor'] as $field) {
            $totals[$field] = (int) $categories->sum($field);
        }
        $totals['remaining_minor'] = $event->budget_target_minor === null ? null : $event->budget_target_minor - $totals['forecast_minor'];

        return ['totals' => $totals, 'categories' => $categories->all()];
    }
}
