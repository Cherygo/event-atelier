<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExpense;
use App\Models\EventExpensePayment;
use App\Models\EventShare;
use App\Models\EventTask;
use App\Models\EventVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SharedEventSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_optional_summaries_are_absent_until_selected_and_never_expose_private_records(): void
    {
        $event = Event::factory()->create(['budget_currency' => 'EUR', 'budget_target_minor' => 100000]);
        $share = EventShare::factory()->for($event)->create();
        $share->publish();
        EventTask::factory()->for($event)->create(['title' => 'Private task title', 'status' => 'completed', 'notes' => 'Private task note']);
        EventTask::factory()->for($event)->create(['status' => 'todo']);
        EventTask::factory()->create(['status' => 'completed']);
        EventVendor::factory()->for($event)->create(['name' => 'Garden Kitchen', 'category' => 'Catering', 'status' => 'booked', 'email' => 'private@example.test', 'notes' => 'Private vendor note', 'quote_details' => 'Private quote']);
        EventVendor::factory()->for($event)->create(['name' => 'Rejected vendor', 'status' => 'declined']);
        EventVendor::factory()->create(['name' => 'Other event vendor', 'status' => 'booked']);
        $expense = EventExpense::factory()->for($event)->create(['title' => 'Private expense title', 'estimated_minor' => 40000, 'actual_minor' => 35025]);
        EventExpense::factory()->for($event)->create(['estimated_minor' => 10050, 'actual_minor' => null]);
        EventExpensePayment::factory()->for($expense, 'expense')->create(['amount_minor' => 10000, 'note' => 'Private payment note']);
        $url = route('shared.show', $share->token);
        $this->get($url)->assertInertia(fn (Assert $page) => $page->missing('plan.progress')->missing('plan.vendors')->missing('plan.budget'));
        $this->actingAs($event->user)->patch(route('events.sharing.update', $event), [
            'show_date' => false, 'show_location' => false, 'public_note' => null,
            'show_progress' => true, 'show_vendors' => true, 'show_budget' => true,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('event_shares', ['event_id' => $event->id, 'show_budget' => true]);
        $response = $this->get($url)->assertInertia(fn (Assert $page) => $page
            ->where('plan.progress', ['total' => 2, 'completed' => 1])
            ->where('plan.vendors', ['total' => 1, 'items' => [['name' => 'Garden Kitchen', 'category' => 'Catering']]])
            ->where('plan.budget', ['currency' => 'EUR', 'target_minor' => 100000, 'forecast_minor' => 45075])
            ->missing('auth')->missing('event'));
        foreach (['Private task', 'Private vendor', 'Private expense', 'Private payment', 'Private quote', 'private@example.test', 'Rejected vendor', 'Other event vendor'] as $secret) {
            $response->assertDontSee($secret);
        }
        $this->patch(route('events.sharing.update', $event), [
            'show_date' => false, 'show_location' => false, 'public_note' => null,
            'show_progress' => false, 'show_vendors' => false, 'show_budget' => false,
        ])->assertSessionHasNoErrors();
        $this->get($url)->assertInertia(fn (Assert $page) => $page->missing('plan.progress')->missing('plan.vendors')->missing('plan.budget'));
    }

    public function test_selected_empty_sections_distinguish_unconfigured_budget_from_zero(): void
    {
        $share = EventShare::factory()->create(['show_progress' => true, 'show_vendors' => true, 'show_budget' => true]);
        $share->publish();
        $this->get(route('shared.show', $share->token))->assertInertia(fn (Assert $page) => $page
            ->where('plan.progress', ['total' => 0, 'completed' => 0])
            ->where('plan.vendors', ['total' => 0, 'items' => []])->where('plan.budget', null));
        $share->event->forceFill(['budget_currency' => 'EUR', 'budget_target_minor' => 0])->save();
        $this->get(route('shared.show', $share->token))->assertInertia(fn (Assert $page) => $page
            ->where('plan.budget', ['currency' => 'EUR', 'target_minor' => 0, 'forecast_minor' => 0]));
    }

    public function test_guest_vendor_list_is_bounded_and_reports_the_complete_count(): void
    {
        $share = EventShare::factory()->create(['show_vendors' => true]);
        $share->publish();
        EventVendor::factory()->for($share->event)->count(51)->create(['status' => 'booked']);
        $this->get(route('shared.show', $share->token))->assertInertia(fn (Assert $page) => $page->where('plan.vendors.total', 51)->has('plan.vendors.items', 50));
    }

    public function test_summary_choices_reject_non_boolean_input(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->patch(route('events.sharing.update', $event), [
            'show_date' => false, 'show_location' => false, 'public_note' => null,
            'show_progress' => 'yes', 'show_vendors' => [], 'show_budget' => 'public',
        ])->assertSessionHasErrors(['show_progress', 'show_vendors', 'show_budget']);
        $this->assertDatabaseCount('event_shares', 0);
    }
}
