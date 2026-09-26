<?php

namespace App\Http\Controllers;

use App\BudgetAmount;
use App\Http\Requests\UpdateEventBudgetRequest;
use App\Models\Event;
use App\Models\EventExpense;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EventBudgetController extends Controller
{
    public function index(Request $request, Event $event): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:60'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $expenses = $event->expenses();
        if (! empty($filters['search'])) {
            $expenses->whereLike('title', '%'.$filters['search'].'%');
        }
        if (! empty($filters['category'])) {
            $expenses->where('category', $filters['category']);
        }

        return Inertia::render('Events/Budget', [
            'event' => $event->only(['id', 'name', 'budget_currency', 'budget_target_minor']),
            'expenses' => $expenses->with(['payments' => fn (HasMany $query): HasMany => $query->orderByDesc('paid_on')->orderByDesc('id')])
                ->withSum('payments as paid_minor', 'amount_minor')->latest('id')->paginate(12)->withQueryString()
                ->through(fn (EventExpense $expense): array => $expense->budgetData()),
            'today' => now()->toDateString(),
            'estimatedTotal' => (int) $event->expenses()->sum('estimated_minor'),
            'categories' => $event->expenses()->distinct()->orderBy('category')->pluck('category'),
            'filters' => ['search' => $filters['search'] ?? '', 'category' => $filters['category'] ?? ''],
            'currencies' => BudgetAmount::CURRENCIES,
            'currencyLocked' => $event->expenses()->exists(),
            'canEdit' => $request->user()->can('editPlanning', $event),
        ]);
    }

    public function update(UpdateEventBudgetRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $data = $request->validated();
            if ($event->budget_currency !== $data['currency'] && $event->expenses()->exists()) {
                throw ValidationException::withMessages(['currency' => 'Currency cannot change while this budget has expenses. No amounts have been converted.']);
            }
            $event->forceFill([
                'budget_currency' => $data['currency'],
                'budget_target_minor' => $data['target'] === null ? null : BudgetAmount::toMinor((string) $data['target']),
            ])->save();
        });

        return back()->with('status', 'Budget settings saved.');
    }
}
