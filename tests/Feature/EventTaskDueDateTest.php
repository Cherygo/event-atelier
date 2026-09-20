<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventTaskDueDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_date_can_be_set_changed_and_cleared_without_shifting_calendar_day(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->post(route('events.tasks.store', $event), ['title' => 'Visit venue', 'due_date' => '2027-03-28'])
            ->assertSessionHasNoErrors();
        $task = $event->tasks()->sole();
        $this->get(route('events.tasks.index', $event))->assertInertia(fn (Assert $page) => $page->where('tasks.data.0.due_date', '2027-03-28'));
        $this->patch(route('events.tasks.update', [$event, $task]), ['title' => $task->title, 'due_date' => '2027-02-30'])->assertSessionHasErrors('due_date');
        $this->patch(route('events.tasks.update', [$event, $task]), ['title' => $task->title, 'due_date' => '2027-04-01'])->assertSessionHasNoErrors();
        $this->assertSame('2027-04-01', $task->fresh()->due_date->toDateString());
        $this->patch(route('events.tasks.update', [$event, $task]), ['title' => $task->title, 'due_date' => null])->assertSessionHasNoErrors();
        $this->assertNull($task->fresh()->due_date);
    }

    public function test_due_filters_respect_today_boundaries_and_completed_tasks_are_not_overdue(): void
    {
        $this->travelTo(now()->setDate(2027, 4, 10)->startOfDay());
        $event = Event::factory()->create();
        $late = EventTask::factory()->for($event)->create(['due_date' => '2027-04-09']);
        EventTask::factory()->for($event)->create(['due_date' => '2027-04-09', 'status' => 'completed']);
        $today = EventTask::factory()->for($event)->create(['due_date' => '2027-04-10']);
        $next = EventTask::factory()->for($event)->create(['due_date' => '2027-04-17']);
        EventTask::factory()->for($event)->create(['due_date' => '2027-04-18']);
        $unscheduled = EventTask::factory()->for($event)->create();
        $this->actingAs($event->user);
        foreach (['overdue' => $late, 'today' => $today, 'upcoming' => $next, 'unscheduled' => $unscheduled] as $filter => $task) {
            $this->get(route('events.tasks.index', ['event' => $event, 'due' => $filter, 'status' => 'all']))
                ->assertInertia(fn (Assert $page) => $page->where('tasks.total', 1)->where('tasks.data.0.id', $task->id)->where('today', '2027-04-10'));
        }
    }

    public function test_due_date_sort_places_unscheduled_tasks_last(): void
    {
        $event = Event::factory()->create();
        $late = EventTask::factory()->for($event)->create(['due_date' => '2027-04-09']);
        $next = EventTask::factory()->for($event)->create(['due_date' => '2027-04-17']);
        $unscheduled = EventTask::factory()->for($event)->create();
        $this->actingAs($event->user)->get(route('events.tasks.index', ['event' => $event, 'sort' => 'due']))
            ->assertInertia(fn (Assert $page) => $page->where('tasks.data.0.id', $late->id)
                ->where('tasks.data.1.id', $next->id)->where('tasks.data.2.id', $unscheduled->id));
    }
}
