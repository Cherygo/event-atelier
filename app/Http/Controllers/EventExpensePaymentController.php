<?php

namespace App\Http\Controllers;

use App\BudgetAmount;
use App\Http\Requests\StoreEventExpensePaymentRequest;
use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventExpensePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EventExpensePaymentController extends Controller
{
    public function store(StoreEventExpensePaymentRequest $request, Event $event, EventExpense $expense): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $expense): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $expense = $event->expenses()->findOrFail($expense->id);
            $data = $request->validated();
            if ($data['currency'] !== $event->budget_currency) {
                throw ValidationException::withMessages(['currency' => 'The budget currency has changed. Reload this page before recording a payment.']);
            }
            if ($expense->actual_minor === null) {
                throw ValidationException::withMessages(['amount' => 'Set the actual cost before recording a payment.']);
            }
            $amount = BudgetAmount::toMinor((string) $data['amount']);
            $paid = (int) $expense->payments()->sum('amount_minor');
            if ($amount > $expense->actual_minor - $paid) {
                throw ValidationException::withMessages(['amount' => 'This payment exceeds the outstanding balance. Check the amount or update the actual cost.']);
            }
            $expense->payments()->create(['amount_minor' => $amount, 'paid_on' => $data['paid_on'], 'note' => $data['note'] ?? null]);
        });

        return back()->with('status', 'Payment recorded. No money has been sent.');
    }

    public function destroy(Event $event, EventExpense $expense, EventExpensePayment $payment): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $expense, $payment): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->expenses()->findOrFail($expense->id)->payments()->findOrFail($payment->id)->delete();
        });

        return back()->with('status', 'Payment record removed. No refund has been issued.');
    }
}
