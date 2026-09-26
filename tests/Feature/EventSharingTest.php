<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_saves_choices_without_publishing_and_cannot_inject_link_or_event_fields(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->patch(route('events.sharing.update', $event), [
            'show_date' => true, 'show_location' => false, 'public_note' => 'Welcome to the garden.',
            'event_id' => 999, 'token' => 'injected', 'token_hash' => 'injected',
        ])->assertRedirect(route('events.sharing.index', $event))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('event_shares', ['event_id' => $event->id, 'show_date' => true, 'show_location' => false, 'public_note' => 'Welcome to the garden.', 'token_hash' => null]);
        $this->get(route('events.sharing.index', $event))->assertInertia(fn (Assert $page) => $page
            ->component('Events/Sharing')->where('canManage', true)->where('shareUrl', null)->where('settings.show_date', true));
    }

    public function test_publish_is_idempotent_and_guests_receive_only_explicitly_shared_fields(): void
    {
        $event = Event::factory()->create(['name' => 'Autumn gathering', 'location' => 'Private venue', 'event_date' => '2026-10-20', 'guest_count' => 90]);
        $this->actingAs($event->user)->post(route('events.sharing.store', $event))->assertRedirect(route('events.sharing.index', $event));
        $share = $event->share;
        $token = $share->token;
        $this->post(route('events.sharing.store', $event))->assertRedirect();
        $this->assertSame($token, $share->fresh()->token);
        $url = route('shared.show', $token);
        $this->get(route('events.sharing.index', $event))->assertInertia(fn (Assert $page) => $page->where('shareUrl', $url));
        $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertInertia(fn (Assert $page) => $page->component('Shared/Show')
                ->where('plan', ['name' => 'Autumn gathering', 'date' => null, 'location' => null, 'note' => null])
                ->missing('auth')->missing('flash')->missing('event')->missing('settings'));
        auth()->logout();
        $this->get($url)->assertOk()->assertDontSee('Private venue');
    }

    public function test_saving_choices_updates_the_live_page_and_turning_them_off_removes_data(): void
    {
        $event = Event::factory()->create(['event_date' => '2026-10-20', 'location' => 'The garden']);
        $share = EventShare::factory()->for($event)->create();
        $share->publish();
        $url = route('shared.show', $share->token);
        $this->actingAs($event->user)->patch(route('events.sharing.update', $event), ['show_date' => true, 'show_location' => true, 'public_note' => 'Join us.'])->assertSessionHasNoErrors();
        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('plan.date', '2026-10-20')->where('plan.location', 'The garden')->where('plan.note', 'Join us.'));
        $this->patch(route('events.sharing.update', $event), ['show_date' => false, 'show_location' => false, 'public_note' => null])->assertSessionHasNoErrors();
        $this->get($url)->assertInertia(fn (Assert $page) => $page->where('plan.date', null)->where('plan.location', null)->where('plan.note', null));
    }

    public function test_revoked_unknown_and_malformed_links_show_the_same_unavailable_page(): void
    {
        $share = EventShare::factory()->create();
        $share->publish();
        $oldUrl = route('shared.show', $share->token);
        $this->actingAs($share->event->user)->delete(route('events.sharing.destroy', $share->event))->assertRedirect();
        $this->assertNull($share->fresh()->token_hash);
        foreach ([$oldUrl, route('shared.show', str_repeat('a', 64)), route('shared.show', 'invalid')] as $url) {
            $response = $this->get($url)->assertNotFound()->assertInertia(fn (Assert $page) => $page->component('Shared/Show')->where('plan', null)->missing('auth'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->post(route('events.sharing.store', $share->event))->assertRedirect();
        $this->get($oldUrl)->assertNotFound();
        $this->get(route('shared.show', $share->fresh()->token))->assertOk();
    }

    public function test_members_cannot_manage_sharing_or_read_the_link_and_outsiders_get_404(): void
    {
        $member = EventMember::factory()->create(['role' => 'admin']);
        $share = EventShare::factory()->for($member->event)->create();
        $share->publish();
        $event = $member->event;
        $this->actingAs($member->user)->get(route('events.sharing.index', $event))->assertInertia(fn (Assert $page) => $page->where('canManage', false)->where('settings', null)->where('shareUrl', null));
        $this->patch(route('events.sharing.update', $event), [])->assertForbidden();
        $this->post(route('events.sharing.store', $event))->assertForbidden();
        $this->delete(route('events.sharing.destroy', $event))->assertForbidden();
        $foreign = Event::factory()->create();
        $this->get(route('events.sharing.index', $foreign))->assertNotFound();
        $this->patch(route('events.sharing.update', $foreign), [])->assertNotFound();
        $this->post(route('events.sharing.store', $foreign))->assertNotFound();
        $this->delete(route('events.sharing.destroy', $foreign))->assertNotFound();
        $this->assertNotNull($share->fresh()->token_hash);
    }

    public function test_guests_cannot_manage_sharing(): void
    {
        $event = Event::factory()->create();
        $this->get(route('events.sharing.index', $event))->assertRedirect(route('login'));
        $this->post(route('events.sharing.store', $event))->assertRedirect(route('login'));
        $this->patch(route('events.sharing.update', $event), [])->assertRedirect(route('login'));
        $this->delete(route('events.sharing.destroy', $event))->assertRedirect(route('login'));
        $this->assertDatabaseCount('event_shares', 0);
    }

    public function test_invalid_sharing_choices_do_not_persist(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->patch(route('events.sharing.update', $event), [])->assertSessionHasErrors(['show_date', 'show_location', 'public_note']);
        $this->patch(route('events.sharing.update', $event), ['show_date' => 'yes', 'show_location' => [], 'public_note' => str_repeat('x', 2001)])
            ->assertSessionHasErrors(['show_date', 'show_location', 'public_note' => 'The public note field must not be greater than 2000 characters.']);
        $this->assertDatabaseCount('event_shares', 0);
    }

    public function test_public_html_escapes_host_content(): void
    {
        $event = Event::factory()->create(['name' => '<script>alert("name")</script>', 'location' => '<img src=x onerror=alert(1)>']);
        $share = EventShare::factory()->for($event)->create(['show_location' => true, 'public_note' => '<script>alert("note")</script>']);
        $share->publish();
        $this->get(route('shared.show', $share->token))->assertOk()->assertDontSee('<script>alert', false)->assertDontSee('<img src=x', false);
    }
}
