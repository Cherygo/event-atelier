<?php

namespace Tests\Feature;

use App\Models\EventExpense;
use App\Models\EventExpensePayment;
use App\Models\EventMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventExpensePaymentTest extends TestCase
{
    use RefreshDatabase;

    public static function editableRoles(): array
    {
        return [['owner'], ['admin'], ['editor']];
    }

    #[DataProvider('editableRoles')]
    public function test_collaborators_can_record_and_remove_payments(string $role): void
    {
        $this->travelTo(now()->setDate(2026, 9, 26));
        $expense = EventExpense::factory()->withActualCost()->create(['actual_minor' => 50001, 'due_date' => '2026-09-25']);
        $event = $expense->event;
        $user = $role === 'owner' ? $event->user : EventMember::factory()->for($event)->create(['role' => $role])->user;
        $this->actingAs($user)->from(route('events.budget.index', $event))->post(route('events.expenses.payments.store', [$event, $expense]), [
            'amount' => '100.01', 'currency' => 'EUR', 'paid_on' => '2026-09-25', 'note' => 'Deposit', 'event_expense_id' => 999,
        ])->assertRedirect(route('events.budget.index', $event))->assertSessionHasNoErrors();
        $payment = $expense->payments()->sole();
        $this->assertSame(10001, $payment->amount_minor);
        $this->assertSame($expense->id, $payment->event_expense_id);
        $this->get(route('events.budget.index', $event))->assertInertia(fn (Assert $page) => $page
            ->where('expenses.data.0.paid_minor', 10001)->where('expenses.data.0.outstanding_minor', 40000)
            ->where('expenses.data.0.payment_status', 'Part-paid')->where('expenses.data.0.overdue', true)
            ->where('expenses.data.0.payments.0.note', 'Deposit')->where('expenses.data.0.payments.0.paid_on', '2026-09-25'));
        $this->delete(route('events.expenses.payments.destroy', [$event, $expense, $payment]))->assertSessionHasNoErrors();
        $this->assertModelMissing($payment);
    }

    public function test_full_payment_clears_overdue_and_further_payments_are_rejected(): void
    {
        $expense = EventExpense::factory()->withActualCost()->create(['actual_minor' => 101, 'due_date' => today()->subDay()]);
        $this->actingAs($expense->event->user)->post(route('events.expenses.payments.store', [$expense->event, $expense]), [
            'amount' => '1.01', 'currency' => 'EUR', 'paid_on' => today()->toDateString(),
        ])->assertSessionHasNoErrors();
        $this->get(route('events.budget.index', $expense->event))->assertInertia(fn (Assert $page) => $page
            ->where('expenses.data.0.payment_status', 'Paid')->where('expenses.data.0.overdue', false)->where('expenses.data.0.outstanding_minor', 0));
        $this->post(route('events.expenses.payments.store', [$expense->event, $expense]), [
            'amount' => '0.01', 'currency' => 'EUR', 'paid_on' => today()->toDateString(),
        ])->assertSessionHasErrors(['amount' => 'This payment exceeds the outstanding balance. Check the amount or update the actual cost.']);
        $this->assertSame(1, $expense->payments()->count());
    }

    public function test_paid_expenses_cannot_be_deleted_or_have_actual_cost_lowered_below_payments(): void
    {
        $payment = EventExpensePayment::factory()->create();
        $expense = $payment->expense;
        $event = $expense->event;
        $this->actingAs($event->user)->delete(route('events.expenses.destroy', [$event, $expense]))->assertSessionHasErrors('expense');
        $data = ['title' => 'Venue', 'category' => 'Venue', 'estimate' => '1000', 'actual' => '249.99', 'currency' => 'EUR'];
        $this->patch(route('events.expenses.update', [$event, $expense]), $data)->assertSessionHasErrors('actual');
        $this->patch(route('events.expenses.update', [$event, $expense]), array_merge($data, ['actual' => null]))->assertSessionHasErrors('actual');
        $this->assertSame(100000, $expense->fresh()->actual_minor);
        $this->assertModelExists($payment);
        $this->patch(route('events.expenses.update', [$event, $expense]), array_merge($data, ['actual' => '250']))->assertSessionHasNoErrors();
        $this->assertSame(25000, $expense->fresh()->actual_minor);
    }

    public function test_unknown_actual_cost_cannot_accept_payments(): void
    {
        $expense = EventExpense::factory()->create();
        $this->actingAs($expense->event->user)->post(route('events.expenses.payments.store', [$expense->event, $expense]), [
            'amount' => '10', 'currency' => 'EUR', 'paid_on' => today()->toDateString(),
        ])->assertSessionHasErrors(['amount' => 'Set the actual cost before recording a payment.']);
        $this->assertSame(0, $expense->payments()->count());
    }

    public function test_viewers_can_read_payment_history_but_cannot_write(): void
    {
        $payment = EventExpensePayment::factory()->create();
        $expense = $payment->expense;
        $member = EventMember::factory()->for($expense->event)->create(['role' => 'viewer']);
        $this->actingAs($member->user)->get(route('events.budget.index', $expense->event))->assertInertia(fn (Assert $page) => $page->where('canEdit', false)->where('expenses.data.0.payments.0.id', $payment->id));
        $this->post(route('events.expenses.payments.store', [$expense->event, $expense]), [])->assertForbidden();
        $this->delete(route('events.expenses.payments.destroy', [$expense->event, $expense, $payment]))->assertForbidden();
        $this->assertModelExists($payment);
    }

    public function test_payment_routes_require_authentication_and_scope_both_parent_records(): void
    {
        $payment = EventExpensePayment::factory()->create();
        $expense = $payment->expense;
        $event = $expense->event;
        $foreign = EventExpensePayment::factory()->create();
        $sibling = EventExpense::factory()->for($event)->create();
        $this->post(route('events.expenses.payments.store', [$event, $expense]), [])->assertRedirect(route('login'));
        $this->delete(route('events.expenses.payments.destroy', [$event, $expense, $payment]))->assertRedirect(route('login'));
        $this->actingAs($event->user)->post(route('events.expenses.payments.store', [$event, $foreign->expense]), [])->assertNotFound();
        $this->delete(route('events.expenses.payments.destroy', [$event, $expense, $foreign]))->assertNotFound();
        $this->delete(route('events.expenses.payments.destroy', [$event, $sibling, $payment]))->assertNotFound();
        $this->post(route('events.expenses.payments.store', [$foreign->expense->event, $foreign->expense]), [])->assertNotFound();
        $this->assertModelExists($payment);
        $this->assertModelExists($foreign);
    }

    public static function invalidPayments(): array
    {
        return [
            'zero' => [['amount' => '0'], 'amount'],
            'negative' => [['amount' => '-1'], 'amount'],
            'precision' => [['amount' => '1.001'], 'amount'],
            'overpayment' => [['amount' => '1000.01'], 'amount'],
            'future date' => [['paid_on' => '2099-01-01'], 'paid_on'],
            'invalid date' => [['paid_on' => '2026-02-30'], 'paid_on'],
            'stale currency' => [['currency' => 'USD'], 'currency'],
            'long note' => [['note' => str_repeat('x', 501)], 'note'],
        ];
    }

    #[DataProvider('invalidPayments')]
    public function test_invalid_payment_records_are_rejected(array $overrides, string $field): void
    {
        $expense = EventExpense::factory()->withActualCost()->create();
        $this->actingAs($expense->event->user)->post(route('events.expenses.payments.store', [$expense->event, $expense]), array_merge([
            'amount' => '100', 'currency' => 'EUR', 'paid_on' => today()->toDateString(),
        ], $overrides))->assertSessionHasErrors($field);
        $this->assertSame(0, $expense->payments()->count());
    }
}
