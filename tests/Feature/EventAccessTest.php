<?php

namespace Tests\Feature;

use App\EventRole;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\EventMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_see_their_shared_events_but_not_unrelated_events(): void
    {
        $member = EventMember::factory()->create();
        $other = Event::factory()->create();

        $this->actingAs($member->user)->get('/events')->assertInertia(fn (Assert $page) => $page
            ->has('events', 1)->where('events.0.id', $member->event_id));
        $this->get(route('events.show', $other))->assertNotFound();
        $this->get(route('events.people', $other))->assertNotFound();
        $this->get(route('events.show', $member->event))->assertOk();
    }

    public function test_viewers_cannot_edit_event_or_members_and_do_not_receive_member_emails(): void
    {
        $member = EventMember::factory()->create();

        $this->actingAs($member->user)->get(route('events.people', $member->event))
            ->assertInertia(fn (Assert $page) => $page->where('can.manageMembers', false)
                ->where('owner.email', null)->has('roles', 0));
        $this->patch(route('events.update', $member->event), ['name' => 'Changed'])->assertForbidden();
        $this->delete(route('events.destroy', $member->event), ['password' => 'password'])->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $member->event_id, 'name' => $member->event->name]);
    }

    public function test_admin_can_edit_details_but_cannot_promote_themselves_or_other_admins(): void
    {
        $admin = EventMember::factory()->create(['role' => EventRole::Admin]);
        $editor = EventMember::factory()->for($admin->event)->create(['role' => EventRole::Editor]);
        $otherAdmin = EventMember::factory()->for($admin->event)->create(['role' => EventRole::Admin]);
        $this->actingAs($admin->user);
        $this->patch(route('events.update', $admin->event), ['name' => 'Updated event', 'type' => 'corporate'])->assertRedirect();
        $this->assertDatabaseHas('events', ['id' => $admin->event_id, 'name' => 'Updated event']);
        $this->patch(route('events.members.update', [$admin->event, $admin]), ['role' => 'owner'])->assertForbidden();
        $this->patch(route('events.members.update', [$admin->event, $editor]), ['role' => 'admin'])->assertForbidden();
        $this->delete(route('events.members.destroy', [$admin->event, $otherAdmin]))->assertForbidden();
        $this->patch(route('events.members.update', [$admin->event, $editor]), ['role' => 'viewer'])->assertRedirect();
        $this->assertDatabaseHas('event_members', ['id' => $editor->id, 'role' => 'viewer']);
    }

    public function test_member_management_is_scoped_and_removed_member_loses_access_immediately(): void
    {
        $member = EventMember::factory()->create();
        $foreign = EventMember::factory()->create();
        $this->actingAs($member->event->user);
        $this->delete(route('events.members.destroy', [$member->event, $foreign]))->assertNotFound();
        $this->patch(route('events.members.update', [$member->event, $member]), ['role' => 'admin'])->assertRedirect();
        $this->assertDatabaseHas('event_members', ['id' => $member->id, 'role' => 'admin']);
        $this->delete(route('events.members.destroy', [$member->event, $member]))->assertRedirect();
        $this->assertDatabaseMissing('event_members', ['id' => $member->id]);
        $this->actingAs($member->user)->get(route('events.show', $member->event))->assertNotFound();
    }

    public function test_ownership_transfer_requires_password_and_existing_member_and_revokes_pending_invitations(): void
    {
        $member = EventMember::factory()->create();
        $event = $member->event;
        $owner = $event->user;
        $foreign = EventMember::factory()->create();
        EventInvitation::factory()->for($event)->create();

        $this->actingAs($owner)->patch(route('events.ownership.update', $event), ['member_id' => $member->id, 'password' => 'wrong'])
            ->assertSessionHasErrors('password');
        $this->patch(route('events.ownership.update', $event), ['member_id' => $foreign->id, 'password' => 'password'])->assertNotFound();
        $this->patch(route('events.ownership.update', $event), ['member_id' => $member->id, 'password' => 'password'])->assertRedirect();
        $event->refresh();
        $this->assertSame($member->user_id, $event->user_id);
        $this->assertSame(EventRole::Admin, $event->roleFor($owner));
        $this->assertSame(EventRole::Owner, $event->roleFor($member->user));
        $this->assertDatabaseMissing('event_members', ['id' => $member->id]);
        $this->assertDatabaseMissing('event_invitations', ['event_id' => $event->id]);
        $this->delete(route('events.destroy', $event), ['password' => 'password'])->assertForbidden();
    }

    public function test_only_owner_can_delete_an_event_with_a_valid_password(): void
    {
        $member = EventMember::factory()->create(['role' => EventRole::Admin]);
        $event = $member->event;
        $this->actingAs($member->user)->delete(route('events.destroy', $event), ['password' => 'password'])->assertForbidden();
        $this->actingAs($event->user)->delete(route('events.destroy', $event), ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->delete(route('events.destroy', $event), ['password' => 'password'])->assertRedirect(route('events.index'));
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
