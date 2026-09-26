<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventExpenseTest extends TestCase
{
    use RefreshDatabase;

    public static function editableRoles(): array
    {
        return [['owner'], ['admin'], ['editor']];
    }

    #[DataProvider('editableRoles')]
    public function test_collaborators_can_manage_expenses_and_budget_settings(string $role): void
    {
        $event = Event::factory()->withBudget()->create();
        $user = $role === 'owner' ? $event->user : EventMember::factory()->for($event)->create(['role' => $role])->user;
        $foreign = Event::factory()->create();
        $this->actingAs($user)->patch(route('events.budget.update', $event), ['currency' => 'EUR', 'target' => '15000'])->assertSessionHasNoErrors();
        $this->assertSame(1500000, $event->fresh()->budget_target_minor);
        $this->post(route('events.expenses.store', $event), [
            'title' => 'Venue hire', 'category' => 'Venue', 'estimate' => '1200.50', 'currency' => 'EUR', 'notes' => 'Includes setup',
            'event_id' => $foreign->id, 'estimated_minor' => 1,
        ])->assertRedirect(route('events.budget.index', $event))->assertSessionHasNoErrors();
        $expense = $event->expenses()->sole();
        $this->assertSame(120050, $expense->estimated_minor);
        $this->assertSame($event->id, $expense->event_id);
        $this->patch(route('events.expenses.update', [$event, $expense]), ['title' => 'Donated venue', 'category' => 'Venue', 'estimate' => '0', 'currency' => 'EUR', 'notes' => null])->assertSessionHasNoErrors();
        $this->assertSame(0, $expense->fresh()->estimated_minor);
        $this->assertNull($expense->fresh()->notes);
        $this->delete(route('events.expenses.destroy', [$event, $expense]))->assertRedirect();
        $this->assertModelMissing($expense);
    }

    public function test_viewer_can_read_but_cannot_write_expenses(): void
    {
        $expense = EventExpense::factory()->create();
        $event = $expense->event;
        $member = EventMember::factory()->for($event)->create(['role' => 'viewer']);
        $this->actingAs($member->user)->get(route('events.budget.index', $event))->assertInertia(fn (Assert $page) => $page->where('canEdit', false)->where('expenses.data.0.id', $expense->id));
        $this->post(route('events.expenses.store', $event), [])->assertForbidden();
        $this->patch(route('events.expenses.update', [$event, $expense]), [])->assertForbidden();
        $this->delete(route('events.expenses.destroy', [$event, $expense]))->assertForbidden();
        $this->assertModelExists($expense);
    }

    public function test_expenses_require_authentication_and_are_scoped_to_the_event(): void
    {
        $event = Event::factory()->withBudget()->create();
        $foreign = EventExpense::factory()->create();
        $this->post(route('events.expenses.store', $event), [])->assertRedirect(route('login'));
        $this->patch(route('events.expenses.update', [$foreign->event, $foreign]), [])->assertRedirect(route('login'));
        $this->delete(route('events.expenses.destroy', [$foreign->event, $foreign]))->assertRedirect(route('login'));
        $this->actingAs($event->user)->post(route('events.expenses.store', $foreign->event), [])->assertNotFound();
        $this->patch(route('events.expenses.update', [$event, $foreign]), [])->assertNotFound();
        $this->delete(route('events.expenses.destroy', [$event, $foreign]))->assertNotFound();
        $this->assertModelExists($foreign);
    }

    public function test_expense_validation_rejects_invalid_fields_and_stale_currency(): void
    {
        $event = Event::factory()->withBudget()->create();
        $this->actingAs($event->user)->post(route('events.expenses.store', $event), [
            'title' => '', 'category' => str_repeat('x', 61), 'estimate' => '12.345', 'currency' => 'BAD', 'notes' => str_repeat('x', 5001),
        ])->assertSessionHasErrors(['title', 'category', 'estimate' => 'Enter an estimate up to 999999999.99 with at most two decimal places.', 'currency', 'notes']);
        $this->post(route('events.expenses.store', $event), ['title' => 'Venue', 'category' => 'Venue', 'estimate' => '10', 'currency' => 'USD'])->assertSessionHasErrors('currency');
        $unconfigured = Event::factory()->for($event->user)->create();
        $this->post(route('events.expenses.store', $unconfigured), ['title' => 'Venue', 'category' => 'Venue', 'estimate' => '10', 'currency' => 'EUR'])->assertSessionHasErrors('currency');
        $this->assertSame(0, EventExpense::query()->count());
    }

    public function test_ledger_filters_are_scoped_and_do_not_change_totals(): void
    {
        $event = Event::factory()->withBudget()->create();
        EventExpense::factory()->for($event)->count(13)->create(['title' => 'Garden detail', 'category' => 'Venue', 'estimated_minor' => 101]);
        EventExpense::factory()->for($event)->create(['title' => 'Lunch', 'category' => 'Catering', 'estimated_minor' => 200]);
        EventExpense::factory()->create(['category' => 'Private', 'estimated_minor' => 999999]);
        $this->actingAs($event->user)->get(route('events.budget.index', ['event' => $event, 'search' => 'garden', 'category' => 'Venue', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->where('expenses.total', 13)->has('expenses.data', 1)->where('budget.totals.estimated_minor', 1513)->where('categories', ['Catering', 'Venue']));
        $this->get(route('events.budget.index', ['event' => $event, 'search' => "%' OR 1=1 --"]))->assertInertia(fn (Assert $page) => $page->where('expenses.total', 0));
    }
}
