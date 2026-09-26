<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventExpensePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventBudgetSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_totals_use_exact_amounts_without_duplicating_expenses_with_multiple_payments(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 26));
        $event = Event::factory()->withBudget()->create(['budget_target_minor' => 100000]);
        $venue = EventExpense::factory()->for($event)->create(['title' => 'Garden', 'category' => 'Venue', 'estimated_minor' => 60000, 'actual_minor' => 65001, 'due_date' => '2026-09-25']);
        EventExpensePayment::factory()->for($venue, 'expense')->create(['amount_minor' => 10001]);
        EventExpensePayment::factory()->for($venue, 'expense')->create(['amount_minor' => 5000]);
        EventExpense::factory()->for($event)->create(['title' => 'Food', 'category' => 'Catering', 'estimated_minor' => 30000, 'actual_minor' => null]);
        EventExpense::factory()->for($event)->create(['title' => 'Donated flowers', 'category' => 'Flowers', 'estimated_minor' => 5000, 'actual_minor' => 0, 'due_date' => '2026-09-25']);
        $room = EventExpense::factory()->for($event)->create(['title' => 'Room', 'category' => 'Venue', 'estimated_minor' => 10000, 'actual_minor' => 10000, 'due_date' => '2026-09-26']);
        EventExpensePayment::factory()->for($room, 'expense')->create(['amount_minor' => 10000]);
        EventExpensePayment::factory()->create();

        $this->actingAs($event->user)->get(route('events.budget.index', ['event' => $event, 'search' => 'Food']))->assertInertia(fn (Assert $page) => $page
            ->where('expenses.total', 1)->where('budget.totals', [
                'expense_count' => 4, 'estimated_minor' => 105000, 'actual_minor' => 75001, 'forecast_minor' => 105001,
                'paid_minor' => 25001, 'outstanding_minor' => 50000, 'unconfirmed_count' => 1, 'overdue_count' => 1, 'remaining_minor' => -5001,
            ])->has('budget.categories', 3)->where('budget.categories.2.category', 'Venue')
            ->where('budget.categories.2.actual_minor', 75001)->where('budget.categories.2.paid_minor', 25001)
            ->where('expenses.data.0.payment_status', 'Unconfirmed')->where('expenses.data.0.outstanding_minor', null));
        $this->get(route('events.overview', $event))->assertInertia(fn (Assert $page) => $page
            ->where('metrics.total', 0)->where('budget.forecast_minor', 105001)->where('budget.remaining_minor', -5001)->where('budget.overdue_count', 1));
        $this->get(route('events.budget.index', ['event' => $event, 'search' => 'Donated']))->assertInertia(fn (Assert $page) => $page
            ->where('expenses.data.0.payment_status', 'No cost')->where('expenses.data.0.outstanding_minor', 0)->where('expenses.data.0.overdue', false));
    }

    public function test_empty_budget_has_zero_totals_without_inventing_a_target(): void
    {
        $event = Event::factory()->withBudget()->create(['budget_target_minor' => null]);
        $this->actingAs($event->user)->get(route('events.budget.index', $event))->assertInertia(fn (Assert $page) => $page
            ->where('budget.totals.forecast_minor', 0)->where('budget.totals.paid_minor', 0)->where('budget.totals.remaining_minor', null)->has('budget.categories', 0));
    }

    public function test_overview_does_not_invent_a_budget_for_an_unconfigured_event(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->get(route('events.overview', $event))->assertInertia(fn (Assert $page) => $page->where('budget', null));
    }
}
