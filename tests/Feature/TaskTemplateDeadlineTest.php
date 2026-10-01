<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TaskTemplateDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_and_import_use_the_same_dates_and_leave_past_suggestions_unscheduled(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 1)->startOfDay());
        $event = Event::factory()->create(['event_date' => '2026-10-31']);
        $this->actingAs($event->user)->get(route('events.tasks.index', $event))->assertInertia(fn (Assert $page) => $page
            ->where('event.event_date', '2026-10-31')
            ->where('templates.2.items.0.due_date', null)
            ->where('templates.2.items.4.due_date', '2026-10-01')
            ->where('templates.2.items.7.due_date', '2026-10-24'));
        $this->post(route('events.task-templates.store', $event), [
            'template' => 'private', 'items' => ['outline-budget', 'catering', 'guest-details'],
            'include_due_dates' => true, 'event_date' => '2026-10-31', 'preview_today' => '2026-10-01',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'template_key' => 'outline-budget', 'due_date' => null]);
        $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'template_key' => 'catering', 'due_date' => '2026-10-01']);
        $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'template_key' => 'guest-details', 'due_date' => '2026-10-24']);
        $event->update(['event_date' => '2026-12-31']);
        $this->assertSame('2026-10-24', $event->tasks()->where('template_key', 'guest-details')->sole()->due_date->toDateString());
    }

    public function test_missing_event_date_allows_unscheduled_import_but_not_automatic_deadlines(): void
    {
        $event = Event::factory()->create(['event_date' => null]);
        $this->actingAs($event->user)->post(route('events.task-templates.store', $event), [
            'template' => 'private', 'items' => ['venue'], 'include_due_dates' => true, 'event_date' => null, 'preview_today' => today()->toDateString(),
        ])->assertSessionHasErrors('include_due_dates');
        $this->assertSame(0, $event->tasks()->count());
        $this->post(route('events.task-templates.store', $event), ['template' => 'private', 'items' => ['venue'], 'include_due_dates' => false])->assertSessionHasNoErrors();
        $this->assertNull($event->tasks()->sole()->due_date);
    }

    public function test_stale_preview_rejects_import_without_partial_writes(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 1)->startOfDay());
        $event = Event::factory()->create(['event_date' => '2026-12-31']);
        $payload = ['template' => 'corporate', 'items' => ['venue', 'corporate-av'], 'include_due_dates' => true, 'event_date' => '2026-12-30', 'preview_today' => '2026-10-01'];
        $this->actingAs($event->user)->post(route('events.task-templates.store', $event), $payload)->assertSessionHasErrors([
            'include_due_dates' => 'The event date or today’s date has changed, or no event date is set. Reload suggestions before adding deadlines.',
        ]);
        $payload['event_date'] = '2026-12-31';
        $payload['preview_today'] = '2026-09-30';
        $this->post(route('events.task-templates.store', $event), $payload)->assertSessionHasErrors('include_due_dates');
        $this->assertSame(0, $event->tasks()->count());
    }

    public function test_suggestions_handle_leap_year_and_year_boundaries_without_mutating_event_date(): void
    {
        $this->travelTo(now()->setDate(2027, 12, 1)->startOfDay());
        $event = Event::factory()->create(['event_date' => '2028-03-01']);
        $this->actingAs($event->user)->post(route('events.task-templates.store', $event), [
            'template' => 'private', 'items' => ['outline-budget', 'catering'], 'include_due_dates' => true, 'event_date' => '2028-03-01', 'preview_today' => '2027-12-01',
        ])->assertSessionHasNoErrors();
        $this->assertSame('2028-03-01', $event->fresh()->event_date->toDateString());
        $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'template_key' => 'outline-budget', 'due_date' => '2027-12-02']);
        $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'template_key' => 'catering', 'due_date' => '2028-01-31']);
    }

    public function test_dates_remain_optional_with_future_event_and_invalid_date_controls_are_rejected(): void
    {
        $event = Event::factory()->create(['event_date' => '2030-12-31']);
        $this->actingAs($event->user)->post(route('events.task-templates.store', $event), [
            'template' => 'private', 'items' => ['venue'], 'include_due_dates' => 'yes', 'event_date' => 'bad', 'preview_today' => 'yesterday',
        ])->assertSessionHasErrors(['include_due_dates', 'event_date', 'preview_today']);
        $this->assertSame(0, $event->tasks()->count());
        $this->post(route('events.task-templates.store', $event), ['template' => 'private', 'items' => ['venue'], 'include_due_dates' => false])->assertSessionHasNoErrors();
        $this->assertNull($event->tasks()->sole()->due_date);
    }
}
