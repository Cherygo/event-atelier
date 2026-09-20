<?php

namespace Tests\Feature;

use App\Models\EventMember;
use App\Models\EventTask;
use App\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventTaskStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_move_through_statuses_and_can_be_reopened(): void
    {
        $this->travelTo(now()->startOfSecond());
        $task = EventTask::factory()->create();
        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
        $this->actingAs($task->event->user);
        $url = route('events.tasks.status', [$task->event, $task]);
        $this->patch($url, ['status' => 'in_progress'])->assertRedirect();
        $this->assertSame(TaskStatus::InProgress, $task->fresh()->status);
        $this->patch($url, ['status' => 'completed'])->assertRedirect();
        $completedAt = $task->fresh()->completed_at;
        $this->assertTrue(now()->equalTo($completedAt));
        $this->travel(1)->hours();
        $this->patch($url, ['status' => 'completed'])->assertRedirect();
        $this->assertTrue($completedAt->equalTo($task->fresh()->completed_at));
        $this->patch($url, ['status' => 'todo'])->assertRedirect();
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_status_filter_combines_with_search_and_active_excludes_completed(): void
    {
        $task = EventTask::factory()->create(['title' => 'Venue research']);
        EventTask::factory()->for($task->event)->create(['title' => 'Venue visit', 'status' => 'completed']);
        EventTask::factory()->for($task->event)->create(['title' => 'Catering', 'status' => 'in_progress']);
        $this->actingAs($task->event->user)->get(route('events.tasks.index', $task->event))
            ->assertInertia(fn (Assert $page) => $page->where('tasks.total', 2)->where('filters.status', 'active'));
        $this->get(route('events.tasks.index', ['event' => $task->event, 'status' => 'completed', 'search' => 'Venue']))
            ->assertInertia(fn (Assert $page) => $page->where('tasks.total', 1)->where('tasks.data.0.title', 'Venue visit'));
    }

    public function test_invalid_unauthorized_and_cross_event_status_changes_are_rejected(): void
    {
        $task = EventTask::factory()->create();
        $this->actingAs($task->event->user)->patch(route('events.tasks.status', [$task->event, $task]), ['status' => 'unknown'])
            ->assertSessionHasErrors('status');
        $foreign = EventTask::factory()->create();
        $this->patch(route('events.tasks.status', [$task->event, $foreign]), ['status' => 'completed'])->assertNotFound();
        $viewer = EventMember::factory()->for($task->event)->create(['role' => 'viewer']);
        $this->actingAs($viewer->user)->patch(route('events.tasks.status', [$task->event, $task]), ['status' => 'completed'])->assertForbidden();
        $this->assertSame(TaskStatus::Todo, $task->fresh()->status);
    }
}
