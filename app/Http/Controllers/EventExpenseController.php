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
            $expense = $event->expenses()->findOrFail($expense->id);
            $attributes = $this->expenseData($request, $event);
            $paid = (int) $expense->payments()->sum('amount_minor');
            if (array_key_exists('actual_minor', $attributes) && $paid > 0 && ($attributes['actual_minor'] === null || $attributes['actual_minor'] < $paid)) {
                throw ValidationException::withMessages(['actual' => 'The actual cost cannot be less than recorded payments. Correct the payment records first.']);
            }
            $expense->update($attributes);
        });

        return back()->with('status', 'Expense saved.');
    }

    public function destroy(Event $event, EventExpense $expense): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $expense): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $expense = $event->expenses()->findOrFail($expense->id);
            if ($expense->payments()->exists()) {
                throw ValidationException::withMessages(['expense' => 'This expense has recorded payments. Remove those records first if you need to delete it.']);
            }
            $expense->delete();
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
