<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventTaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_survives_ownership_transfer_and_viewers_cannot_reassign(): void
    {
        $event = Event::factory()->create();
        $member = EventMember::factory()->for($event)->create(['role' => 'editor']);
        $task = EventTask::factory()->for($event)->create(['assigned_to' => $member->user_id]);
        $this->actingAs($event->user)->patch(route('events.ownership.update', $event), ['member_id' => $member->id, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame($member->user_id, $task->fresh()->assigned_to);
        $viewer = EventMember::factory()->for($event)->create(['role' => 'viewer']);
        $this->actingAs($viewer->user)->patch(route('events.tasks.update', [$event, $task]), ['title' => $task->title, 'assigned_to' => null])->assertForbidden();
        $this->assertSame($member->user_id, $task->fresh()->assigned_to);
    }

    public function test_only_people_with_editing_access_can_receive_tasks(): void
    {
        $event = Event::factory()->create();
        $editor = EventMember::factory()->for($event)->create(['role' => 'editor']);
        $viewer = EventMember::factory()->for($event)->create(['role' => 'viewer']);
        $outsider = User::factory()->create();
        $this->actingAs($event->user);
        foreach ([$event->user_id, $editor->user_id] as $id) {
            $this->post(route('events.tasks.store', $event), ['title' => 'Confirm venue', 'assigned_to' => $id])->assertSessionHasNoErrors();
            $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'assigned_to' => $id]);
        }
        foreach ([$viewer->user_id, $outsider->id] as $id) {
            $this->post(route('events.tasks.store', $event), ['title' => 'Invalid assignment', 'assigned_to' => $id])->assertSessionHasErrors('assigned_to');
        }
        $task = $event->tasks()->first();
        $this->patch(route('events.tasks.update', [$event, $task]), ['title' => $task->title, 'assigned_to' => null])->assertSessionHasNoErrors();
        $this->assertNull($task->fresh()->assigned_to);
        $this->get(route('events.tasks.index', $event))->assertInertia(fn (Assert $page) => $page->has('assignees', 2)->missing('assignees.0.email'));
    }

    public function test_removing_or_downgrading_a_member_unassigns_their_tasks(): void
    {
        $event = Event::factory()->create();
        foreach (['remove', 'downgrade'] as $action) {
            $member = EventMember::factory()->for($event)->create(['role' => 'editor']);
            $task = EventTask::factory()->for($event)->create(['assigned_to' => $member->user_id]);
            $this->actingAs($event->user);
            if ($action === 'remove') {
                $this->delete(route('events.members.destroy', [$event, $member]))->assertSessionHasNoErrors();
            } else {
                $this->patch(route('events.members.update', [$event, $member]), ['role' => 'viewer'])->assertSessionHasNoErrors();
            }
            $this->assertNull($task->fresh()->assigned_to);
        }
    }
}
