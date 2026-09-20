<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventTaskTest extends TestCase
{
    use RefreshDatabase;

    public static function editableRoles(): array
    {
        return [['owner'], ['admin'], ['editor']];
    }

    #[DataProvider('editableRoles')]
    public function test_planners_can_create_edit_and_delete_tasks(string $role): void
    {
        $event = Event::factory()->create();
        $user = $role === 'owner' ? $event->user : User::factory()->create();
        if ($role !== 'owner') {
            EventMember::factory()->for($event)->for($user)->create(['role' => $role]);
        }
        $other = Event::factory()->create();
        $this->actingAs($user)->post(route('events.tasks.store', $event), [
            'title' => 'Book the venue', 'notes' => 'Ask about access', 'category' => 'Venue', 'event_id' => $other->id,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $task = $event->tasks()->sole();
        $this->assertSame('Book the venue', $task->title);
        $this->assertSame($event->id, $task->event_id);
        $this->patch(route('events.tasks.update', [$event, $task]), ['title' => 'Visit the venue', 'notes' => null])->assertRedirect();
        $this->assertSame('Visit the venue', $task->fresh()->title);
        $this->assertNull($task->fresh()->notes);
        $this->delete(route('events.tasks.destroy', [$event, $task]))->assertRedirect();
        $this->assertDatabaseMissing('event_tasks', ['id' => $task->id]);
    }

    public function test_viewer_can_read_but_cannot_change_tasks(): void
    {
        $member = EventMember::factory()->create(['role' => 'viewer']);
        $task = EventTask::factory()->for($member->event)->create();
        $this->actingAs($member->user)->get(route('events.tasks.index', $member->event))
            ->assertInertia(fn (Assert $page) => $page->where('canEdit', false)->where('tasks.data.0.id', $task->id));
        $this->post(route('events.tasks.store', $member->event), ['title' => 'No'])->assertForbidden();
        $this->patch(route('events.tasks.update', [$member->event, $task]), ['title' => 'No'])->assertForbidden();
        $this->delete(route('events.tasks.destroy', [$member->event, $task]))->assertForbidden();
        $this->assertDatabaseHas('event_tasks', ['id' => $task->id, 'title' => $task->title]);
    }

    public function test_tasks_cannot_be_read_or_changed_across_events(): void
    {
        $event = Event::factory()->create();
        $foreign = EventTask::factory()->create();
        $this->get(route('events.tasks.index', $event))->assertRedirect(route('login'));
        $this->actingAs($event->user)->get(route('events.tasks.index', $foreign->event))->assertNotFound();
        $this->patch(route('events.tasks.update', [$event, $foreign]), ['title' => 'No'])->assertNotFound();
        $this->delete(route('events.tasks.destroy', [$event, $foreign]))->assertNotFound();
    }

    public function test_tasks_validate_content_and_paginate_search_within_the_event(): void
    {
        $event = Event::factory()->create();
        EventTask::factory()->for($event)->count(22)->create(['title' => 'Venue research']);
        EventTask::factory()->create(['title' => 'Venue foreign']);
        $this->actingAs($event->user)->post(route('events.tasks.store', $event), [
            'title' => '', 'notes' => str_repeat('a', 5001), 'category' => str_repeat('a', 61),
        ])->assertSessionHasErrors(['title', 'notes', 'category']);
        $this->get(route('events.tasks.index', ['event' => $event, 'search' => 'Venue', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->where('tasks.total', 22)->has('tasks.data', 2)->where('filters.search', 'Venue'));
    }
}
