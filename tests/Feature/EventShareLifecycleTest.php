<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventShareLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_shows_saved_guest_data_without_publishing(): void
    {
        $event = Event::factory()->create();
        $preview = route('events.sharing.preview', $event);
        $this->actingAs($event->user)->get($preview)->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Shared/Show')->where('plan.name', $event->name)->where('plan.location', null)
            ->where('backUrl', route('events.sharing.index', $event))->missing('auth'));
        $this->assertDatabaseCount('event_shares', 0);
        $share = EventShare::factory()->for($event)->create(['show_location' => true, 'public_note' => 'Saved note']);
        $this->get($preview)->assertInertia(fn (Assert $page) => $page->where('plan.location', $event->location)->where('plan.note', 'Saved note'));
        $this->assertNull($share->fresh()->token_hash);
    }

    public function test_replacing_link_revokes_old_access_without_changing_content(): void
    {
        $share = EventShare::factory()->create(['public_note' => 'Welcome']);
        $share->publish();
        $oldToken = $share->token;
        $this->actingAs($share->event->user)->post(route('events.sharing.rotate', $share->event))->assertRedirect(route('events.sharing.index', $share->event));
        $this->assertNotSame($oldToken, $share->fresh()->token);
        $this->assertSame('Welcome', $share->fresh()->public_note);
        $this->get(route('shared.show', $oldToken))->assertNotFound();
        $this->get(route('shared.show', $share->fresh()->token))->assertOk()->assertInertia(fn (Assert $page) => $page->where('plan.note', 'Welcome')->missing('backUrl'));
    }

    public function test_replacing_a_private_link_cannot_publish_it(): void
    {
        $share = EventShare::factory()->create();
        $this->actingAs($share->event->user)->post(route('events.sharing.rotate', $share->event))->assertStatus(409);
        $this->assertNull($share->fresh()->token_hash);
    }

    public function test_preview_and_replacement_require_event_ownership(): void
    {
        $member = EventMember::factory()->create(['role' => 'admin']);
        $event = $member->event;
        $this->get(route('events.sharing.preview', $event))->assertRedirect(route('login'));
        $this->post(route('events.sharing.rotate', $event))->assertRedirect(route('login'));
        $this->actingAs($member->user)->get(route('events.sharing.preview', $event))->assertForbidden();
        $this->post(route('events.sharing.rotate', $event))->assertForbidden();
        $foreign = Event::factory()->create();
        $this->get(route('events.sharing.preview', $foreign))->assertNotFound();
        $this->post(route('events.sharing.rotate', $foreign))->assertNotFound();
    }

    public function test_ownership_transfer_revokes_public_access_and_only_new_owner_can_republish(): void
    {
        $member = EventMember::factory()->create();
        $event = $member->event;
        $share = EventShare::factory()->for($event)->create(['show_budget' => true]);
        $share->publish();
        $oldUrl = route('shared.show', $share->token);
        $this->actingAs($event->user)->patch(route('events.ownership.update', $event), ['member_id' => $member->id, 'password' => 'password'])->assertRedirect();
        $this->assertNull($share->fresh()->token_hash);
        $this->get($oldUrl)->assertNotFound();
        $this->post(route('events.sharing.store', $event))->assertForbidden();
        $this->actingAs($member->user)->get(route('events.sharing.index', $event))->assertInertia(fn (Assert $page) => $page->where('canManage', true)->where('shareUrl', null));
        $this->post(route('events.sharing.store', $event))->assertRedirect();
        $this->get($oldUrl)->assertNotFound();
        $this->get(route('shared.show', $share->fresh()->token))->assertOk();
    }
}
