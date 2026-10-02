<?php

namespace Tests\Feature;

use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalPlanningJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_local_workspace_can_be_planned_collaboratively_and_shared_without_private_details(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 2)->startOfDay());
        Mail::fake();
        $this->post('/register', [
            'name' => 'Local Host', 'email' => 'host@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors()->assertRedirect('/dashboard');
        $owner = User::where('email', 'host@example.test')->sole();
        $this->post(route('events.store'), ['name' => 'Winter gathering', 'type' => 'private', 'event_date' => '2026-12-15'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $event = Event::where('user_id', $owner->id)->sole();

        $this->post(route('events.task-templates.store', $event), [
            'template' => 'private', 'items' => ['guest-details'], 'include_due_dates' => true,
            'event_date' => '2026-12-15', 'preview_today' => '2026-10-02',
        ])->assertSessionHasNoErrors();
        $task = $event->tasks()->sole();
        $this->assertSame('2026-12-08', $task->due_date->toDateString());

        $this->post(route('events.vendors.store', $event), [
            'name' => 'Garden Kitchen', 'category' => 'Catering', 'status' => 'booked',
            'email' => 'private-contact@example.test', 'notes' => 'Internal negotiation',
            'quote_amount' => '450.00', 'currency' => 'EUR',
        ])->assertSessionHasNoErrors();
        $this->patch(route('events.budget.update', $event), ['currency' => 'EUR', 'target' => '1000.00'])->assertSessionHasNoErrors();
        $this->post(route('events.expenses.store', $event), [
            'title' => 'Dinner', 'category' => 'Catering', 'estimate' => '400.00', 'actual' => '450.00',
            'currency' => 'EUR', 'notes' => 'Internal invoice',
        ])->assertSessionHasNoErrors();
        $expense = $event->expenses()->sole();
        $this->post(route('events.expenses.payments.store', [$event, $expense]), [
            'amount' => '100.00', 'currency' => 'EUR', 'paid_on' => '2026-10-02', 'note' => 'Private deposit',
        ])->assertSessionHasNoErrors();

        $this->post(route('events.invitations.store', $event), ['email' => 'editor@example.test', 'role' => 'editor'])->assertSessionHasNoErrors();
        Mail::assertSent(EventInvitationMail::class, 1);
        $invitationUrl = Mail::sent(EventInvitationMail::class)->sole()->acceptUrl;
        $this->post('/logout');
        $this->get($invitationUrl)->assertOk();
        $this->post('/register', [
            'name' => 'Local Editor', 'email' => 'editor@example.test',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors()->assertRedirect($invitationUrl);
        $this->post(route('invitations.accept', basename($invitationUrl)))->assertRedirect(route('events.show', $event));
        $editor = User::where('email', 'editor@example.test')->sole();
        $this->put(route('events.tasks.update', [$event, $task]), [
            'title' => 'Send arrival instructions', 'assigned_to' => $editor->id, 'notes' => 'Private access code',
        ])->assertSessionHasNoErrors();
        $this->patch(route('events.tasks.status', [$event, $task]), ['status' => 'completed'])->assertSessionHasNoErrors();

        Auth::forgetGuards();
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('auth.user.id', $editor->id));
        $this->get(route('events.overview', $event))->assertInertia(fn (Assert $page) => $page
            ->where('metrics.completed', 1)->where('metrics.percent', 100)
            ->where('budget.forecast_minor', 45000)->where('budget.paid_minor', 10000)->where('budget.remaining_minor', 55000));

        $this->actingAs($owner)->patch(route('events.sharing.update', $event), [
            'show_date' => true, 'show_location' => false, 'public_note' => 'Welcome!',
            'show_progress' => true, 'show_vendors' => true, 'show_budget' => false,
        ])->assertSessionHasNoErrors();
        $this->post(route('events.sharing.store', $event))->assertSessionHasNoErrors();
        $shareUrl = route('shared.show', $event->fresh()->share->token);
        $this->post('/logout');
        $this->get($shareUrl)->assertInertia(fn (Assert $page) => $page
            ->where('plan.name', 'Winter gathering')->where('plan.progress.completed', 1)
            ->where('plan.vendors.items', [['name' => 'Garden Kitchen', 'category' => 'Catering']])
            ->missing('plan.budget')->missing('auth'))
            ->assertDontSee('private-contact@example.test')->assertDontSee('Private access code')
            ->assertDontSee('Internal invoice')->assertDontSee('Private deposit');

        $this->actingAs($owner)->delete(route('events.members.destroy', [$event, $event->members()->sole()]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('event_tasks', ['id' => $task->id, 'assigned_to' => null, 'status' => 'completed']);
        $this->actingAs($editor)->get(route('events.tasks.index', $event))->assertNotFound();
        $this->actingAs($owner)->delete(route('events.sharing.destroy', $event))->assertSessionHasNoErrors();

        $this->get($shareUrl)->assertNotFound();
    }
}
