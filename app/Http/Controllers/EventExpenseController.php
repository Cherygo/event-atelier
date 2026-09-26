<?php

namespace App\Http\Controllers;

use App\BudgetAmount;
use App\Http\Requests\SaveEventExpenseRequest;
use App\Models\Event;
use App\Models\EventExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EventExpenseController extends Controller
{
    public function store(SaveEventExpenseRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->expenses()->create($this->expenseData($request, $event));
        });

        return to_route('events.budget.index', $event)->with('status', 'Expense added.');
    }

    public function update(SaveEventExpenseRequest $request, Event $event, EventExpense $expense): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $expense): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->expenses()->findOrFail($expense->id)->update($this->expenseData($request, $event));
        });

        return back()->with('status', 'Expense saved.');
    }

    public function destroy(Event $event, EventExpense $expense): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $expense): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->expenses()->findOrFail($expense->id)->delete();
        });

        return to_route('events.budget.index', $event)->with('status', 'Expense deleted.');
    }

    /** @return array{title: string, category: string, estimated_minor: int, notes: ?string, actual_minor?: ?int, due_date?: ?string} */
    private function expenseData(SaveEventExpenseRequest $request, Event $event): array
    {
        $data = $request->validated();
        if ($event->budget_currency === null || $event->budget_currency !== $data['currency']) {
            throw ValidationException::withMessages(['currency' => 'Set the budget currency first, then reload this page before saving an expense.']);
        }

        $attributes = [
            'title' => $data['title'], 'category' => $data['category'],
            'estimated_minor' => BudgetAmount::toMinor((string) $data['estimate']), 'notes' => $data['notes'] ?? null,
        ];
        if (array_key_exists('actual', $data)) {
            $attributes['actual_minor'] = $data['actual'] === null ? null : BudgetAmount::toMinor((string) $data['actual']);
        }
        if (array_key_exists('due_date', $data)) {
            $attributes['due_date'] = $data['due_date'];
        }

        return $attributes;
    }
}
