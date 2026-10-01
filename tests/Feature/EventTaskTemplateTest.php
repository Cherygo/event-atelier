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

class EventTaskTemplateTest extends TestCase
{
    use RefreshDatabase;

    public static function editingRoles(): array
    {
        return [['owner'], ['admin'], ['editor']];
    }

    #[DataProvider('editingRoles')]
    public function test_editors_can_import_only_selected_server_owned_content(string $role): void
    {
        $event = Event::factory()->create();
        $user = $role === 'owner' ? $event->user : User::factory()->create();
        if ($role !== 'owner') {
            EventMember::factory()->for($event)->for($user)->create(['role' => $role]);
        }
        $this->actingAs($user)->post(route('events.task-templates.store', $event), [
            'template' => 'wedding', 'items' => ['venue', 'guest-list'],
            'event_id' => 999, 'title' => 'Injected', 'status' => 'completed', 'assigned_to' => $user->id, 'due_date' => '2026-01-01', 'template_key' => 'other',
        ])->assertRedirect(route('events.tasks.index', ['event' => $event, 'status' => 'all']))->assertSessionHasNoErrors();
        $this->assertSame(2, $event->tasks()->count());
        $this->assertDatabaseHas('event_tasks', ['event_id' => $event->id, 'template_key' => 'venue', 'title' => 'Confirm the venue', 'category' => 'Venue', 'status' => 'todo', 'assigned_to' => null, 'due_date' => null]);
        $this->assertDatabaseMissing('event_tasks', ['title' => 'Injected']);
    }

    public function test_repeated_and_overlapping_imports_preserve_edited_and_completed_tasks(): void
    {
        $event = Event::factory()->create();
        EventTask::factory()->for($event)->create(['title' => 'Manual task']);
        $url = route('events.task-templates.store', $event);
        $this->actingAs($event->user)->post($url, ['template' => 'wedding', 'items' => ['venue']])->assertSessionHasNoErrors();
        $task = $event->tasks()->where('template_key', 'venue')->sole();
        $this->patch(route('events.tasks.update', [$event, $task]), ['title' => 'Our renamed venue task', 'template_key' => null])->assertSessionHasNoErrors();
        $this->patch(route('events.tasks.status', [$event, $task]), ['status' => 'completed'])->assertSessionHasNoErrors();
        $this->post($url, ['template' => 'corporate', 'items' => ['venue', 'corporate-av']])->assertSessionHasNoErrors();
        $this->post($url, ['template' => 'corporate', 'items' => ['venue', 'corporate-av']])->assertSessionHasNoErrors();
        $this->assertSame(3, $event->tasks()->count());
        $this->assertDatabaseHas('event_tasks', ['id' => $task->id, 'template_key' => 'venue', 'title' => 'Our renamed venue task', 'status' => 'completed']);
        $this->get(route('events.tasks.index', ['event' => $event, 'search' => 'Nothing matches']))
            ->assertInertia(fn (Assert $page) => $page->where('tasks.total', 0)->has('templateKeys', 2)->has('templates', 3));
    }

    public function test_imports_are_scoped_per_event_and_deleted_tasks_can_be_added_again(): void
    {
        $event = Event::factory()->create();
        $other = Event::factory()->for($event->user)->create();
        $this->actingAs($event->user);
        foreach ([$event, $other] as $target) {
            $this->post(route('events.task-templates.store', $target), ['template' => 'private', 'items' => ['venue']])->assertSessionHasNoErrors();
        }
        $task = $event->tasks()->sole();
        $this->delete(route('events.tasks.destroy', [$event, $task]))->assertRedirect();
        $this->post(route('events.task-templates.store', $event), ['template' => 'private', 'items' => ['venue']])->assertSessionHasNoErrors();
        $this->assertSame(1, $event->tasks()->count());
        $this->assertSame(1, $other->tasks()->count());
        $this->assertNotSame($task->id, $event->tasks()->sole()->id);
    }

    public function test_import_requires_membership_and_editing_access(): void
    {
        $member = EventMember::factory()->create(['role' => 'viewer']);
        $event = $member->event;
        $payload = ['template' => 'private', 'items' => ['venue']];
        $this->post(route('events.task-templates.store', $event), $payload)->assertRedirect(route('login'));
        $this->actingAs($member->user)->get(route('events.tasks.index', $event))->assertInertia(fn (Assert $page) => $page->where('templates', [])->where('templateKeys', []));
        $this->post(route('events.task-templates.store', $event), $payload)->assertForbidden();
        $this->actingAs(User::factory()->create())->post(route('events.task-templates.store', $event), $payload)->assertNotFound();
        $this->assertSame(0, $event->tasks()->count());
    }

    public static function invalidSelections(): array
    {
        return [
            'no tasks' => [['template' => 'private', 'items' => []], 'items'],
            'unknown template' => [['template' => 'unknown', 'items' => ['venue']], 'template'],
            'invalid template type' => [['template' => [], 'items' => ['venue']], 'template'],
            'unknown item' => [['template' => 'private', 'items' => ['venue', 'fake']], 'items.1'],
            'wrong template item' => [['template' => 'private', 'items' => ['corporate-av']], 'items.0'],
            'duplicate selection' => [['template' => 'private', 'items' => ['venue', 'venue']], 'items.0'],
            'invalid items type' => [['template' => 'private', 'items' => 'venue'], 'items'],
        ];
    }

    #[DataProvider('invalidSelections')]
    public function test_invalid_selections_create_no_tasks(array $payload, string $error): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->post(route('events.task-templates.store', $event), $payload)->assertSessionHasErrors($error);
        $this->assertSame(0, $event->tasks()->count());
    }
}
