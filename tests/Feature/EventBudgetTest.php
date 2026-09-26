<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_setup_preserves_cents_and_can_clear_target(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->from(route('events.budget.index', $event))
            ->patch(route('events.budget.update', $event), ['currency' => 'EUR', 'target' => '12345.67', 'user_id' => 999])
            ->assertRedirect(route('events.budget.index', $event))->assertSessionHasNoErrors();
        $this->assertSame(1234567, $event->fresh()->budget_target_minor);
        $this->assertSame('EUR', $event->fresh()->budget_currency);
        $this->assertSame($event->user_id, $event->fresh()->user_id);
        $this->patch(route('events.budget.update', $event), ['currency' => 'USD', 'target' => null])->assertSessionHasNoErrors();
        $this->assertNull($event->fresh()->budget_target_minor);
        $this->assertSame('USD', $event->fresh()->budget_currency);
    }

    public function test_currency_cannot_relabel_existing_expenses_but_target_can_change(): void
    {
        $expense = EventExpense::factory()->create();
        $event = $expense->event;
        $this->actingAs($event->user)->patch(route('events.budget.update', $event), ['currency' => 'USD', 'target' => '20'])
            ->assertSessionHasErrors(['currency' => 'Currency cannot change while this budget has expenses. No amounts have been converted.']);
        $this->assertSame('EUR', $event->fresh()->budget_currency);
        $this->patch(route('events.budget.update', $event), ['currency' => 'EUR', 'target' => '0'])->assertSessionHasNoErrors();
        $this->assertSame(0, $event->fresh()->budget_target_minor);
    }

    public function test_budget_settings_require_membership_and_edit_permission(): void
    {
        $member = EventMember::factory()->create(['role' => 'viewer']);
        $event = $member->event;
        $this->get(route('events.budget.index', $event))->assertRedirect(route('login'));
        $this->patch(route('events.budget.update', $event), [])->assertRedirect(route('login'));
        $this->actingAs($member->user)->get(route('events.budget.index', $event))
            ->assertInertia(fn (Assert $page) => $page->component('Events/Budget')->where('canEdit', false)->where('event.budget_currency', null));
        $this->patch(route('events.budget.update', $event), ['currency' => 'EUR', 'target' => '10'])->assertForbidden();
        $foreign = Event::factory()->create();
        $this->get(route('events.budget.index', $foreign))->assertNotFound();
        $this->patch(route('events.budget.update', $foreign), ['currency' => 'EUR', 'target' => '10'])->assertNotFound();
        $this->assertNull($event->fresh()->budget_currency);
    }

    public function test_budget_rejects_unsupported_currency_and_invalid_target(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->patch(route('events.budget.update', $event), ['currency' => 'BAD', 'target' => '1.234'])
            ->assertSessionHasErrors(['currency', 'target' => 'Enter a target up to 999999999.99 with at most two decimal places.']);
        $this->assertNull($event->fresh()->budget_currency);
    }
}
