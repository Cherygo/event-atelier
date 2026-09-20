<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_overview_and_access_boundaries(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->get(route('events.overview', $event))->assertInertia(fn (Assert $page) => $page->component('Events/Overview')->where('metrics.total', 0)->where('metrics.percent', 0)->has('attention', 0)->where('canEdit', true));
        $viewer = EventMember::factory()->for($event)->create(['role' => 'viewer']);
        $this->actingAs($viewer->user)->get(route('events.overview', $event))->assertInertia(fn (Assert $page) => $page->where('canEdit', false));
        $this->actingAs(User::factory()->create())->get(route('events.overview', $event))->assertNotFound();
    }

    public function test_metrics_cover_all_tasks_and_only_the_current_event(): void
    {
        $this->travelTo(now()->setDate(2027, 4, 10)->startOfDay());
        $event = Event::factory()->create();
        EventTask::factory()->for($event)->count(21)->create(['status' => 'completed', 'due_date' => '2027-04-01']);
        EventTask::factory()->for($event)->create(['status' => 'in_progress', 'due_date' => '2027-04-09']);
        EventTask::factory()->for($event)->create(['due_date' => '2027-04-10']);
        EventTask::factory()->for($event)->count(7)->create(['due_date' => '2027-04-17']);
        EventTask::factory()->for($event)->create(['due_date' => '2027-04-18']);
        EventTask::factory()->create(['due_date' => '2027-04-01']);
        $this->actingAs($event->user)->get(route('events.overview', $event))->assertInertia(fn (Assert $page) => $page
            ->where('metrics.total', 31)->where('metrics.completed', 21)->where('metrics.in_progress', 1)->where('metrics.todo', 9)
            ->where('metrics.percent', 68)->where('metrics.overdue', 1)->where('metrics.today', 1)->has('attention', 6)
            ->where('attention.0.due_date','2027-04-09'));
    }
}
