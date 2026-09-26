<?php

namespace Tests\Feature;

use App\Models\EventExpense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventExpenseCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_actual_cost_and_due_date_can_be_recorded_and_cleared(): void
    {
        $expense = EventExpense::factory()->create();
        $event = $expense->event;
        $data = ['title' => 'Venue', 'category' => 'Venue', 'estimate' => '100', 'currency' => 'EUR', 'actual' => '120.57', 'due_date' => '2026-11-15'];
        $this->actingAs($event->user)->patch(route('events.expenses.update', [$event, $expense]), $data)->assertSessionHasNoErrors();
        $this->assertSame(12057, $expense->fresh()->actual_minor);
        $this->get(route('events.budget.index', $event))->assertInertia(fn (Assert $page) => $page->where('expenses.data.0.actual_minor', 12057)->where('expenses.data.0.due_date', '2026-11-15'));
        $this->patch(route('events.expenses.update', [$event, $expense]), array_merge($data, ['actual' => '0']))->assertSessionHasNoErrors();
        $this->assertSame(0, $expense->fresh()->actual_minor);
        $this->patch(route('events.expenses.update', [$event, $expense]), array_merge($data, ['actual' => null, 'due_date' => null]))->assertSessionHasNoErrors();
        $this->assertNull($expense->fresh()->actual_minor);
        $this->assertNull($expense->fresh()->due_date);
    }

    public function test_invalid_cost_and_impossible_date_are_rejected(): void
    {
        $expense = EventExpense::factory()->create();
        $this->actingAs($expense->event->user)->patch(route('events.expenses.update', [$expense->event, $expense]), [
            'title' => 'Venue', 'category' => 'Venue', 'estimate' => '100', 'currency' => 'EUR', 'actual' => '-1', 'due_date' => '2026-02-30',
        ])->assertSessionHasErrors(['actual', 'due_date']);
        $this->assertNull($expense->fresh()->actual_minor);
        $this->assertNull($expense->fresh()->due_date);
    }
}
