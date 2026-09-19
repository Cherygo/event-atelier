<?php

namespace Tests\Feature;

use App\EventRole;
use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EventInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_email_an_unregistered_person_who_registers_and_accepts(): void
    {
        Mail::fake();
        $event = Event::factory()->create();
        $email = 'new-person@example.com';
        $this->actingAs($event->user)->post(route('events.invitations.store', $event), ['email' => ' New-Person@Example.com ', 'role' => 'editor'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseMissing('users', ['email' => $email]);
        $this->assertDatabaseCount('event_members', 0);
        Mail::assertSent(EventInvitationMail::class, fn ($mail): bool => $mail->hasTo($email));
        $mail = Mail::sent(EventInvitationMail::class)->first();
        $mail->assertSeeInHtml($event->name);
        $mail->assertSeeInText('create one first');
        $token = basename($mail->acceptUrl);
        $this->assertSame(hash('sha256', $token), EventInvitation::first()->token_hash);

        $this->post('/logout');
        $this->get($mail->acceptUrl)->assertInertia(fn (Assert $page) => $page
            ->component('Invitations/Show')->where('invitation.email', $email));
        $this->post('/register', [
            'name' => 'New Planner', 'email' => $email,
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertRedirect($mail->acceptUrl);
        $this->post(route('invitations.accept', $token))->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('event_members', [
            'event_id' => $event->id, 'user_id' => User::where('email', $email)->first()->id, 'role' => 'editor',
        ]);
        $this->assertDatabaseCount('event_invitations', 0);
        $this->get(route('events.show', $event))->assertOk();
        $this->post(route('invitations.accept', $token))->assertRedirect($mail->acceptUrl);
        $this->assertDatabaseCount('event_members', 1);
    }

    public function test_existing_recipient_returns_from_login_and_wrong_account_cannot_accept(): void
    {
        $recipient = User::factory()->create();
        $invitation = EventInvitation::factory()->create(['email' => strtolower($recipient->email), 'token_hash' => hash('sha256', 'recipient-token')]);
        $url = route('invitations.show', 'recipient-token');
        $this->get($url)->assertOk();
        $this->post('/login', ['email' => $recipient->email, 'password' => 'password'])->assertRedirect($url);
        $this->actingAs(User::factory()->create())->post(route('invitations.accept', 'recipient-token'))
            ->assertSessionHasErrors('invitation');
        $this->assertDatabaseCount('event_members', 0);
        $this->actingAs($recipient)->post(route('invitations.accept', 'recipient-token'))->assertRedirect(route('events.show', $invitation->event));
        $this->assertDatabaseHas('event_members', ['user_id' => $recipient->id, 'event_id' => $invitation->event_id, 'role' => 'viewer']);
    }

    public function test_resending_invalidates_old_link_and_revoking_invalidates_new_link(): void
    {
        Mail::fake();
        $event = Event::factory()->create();
        $recipient = User::factory()->create();
        $this->actingAs($event->user)->post(route('events.invitations.store', $event), ['email' => $recipient->email, 'role' => 'viewer']);
        $old = Mail::sent(EventInvitationMail::class)->first()->acceptUrl;
        $this->post(route('events.invitations.store', $event), ['email' => $recipient->email, 'role' => 'editor']);
        Mail::assertSent(EventInvitationMail::class, 2);
        $new = Mail::sent(EventInvitationMail::class)->last()->acceptUrl;
        $this->assertNotSame($old, $new);
        $this->assertDatabaseCount('event_invitations', 1);
        $this->get($old)->assertInertia(fn (Assert $page) => $page->where('invitation', null));
        $this->delete(route('events.invitations.destroy', [$event, EventInvitation::first()]))->assertRedirect();
        $this->actingAs($recipient)->post(route('invitations.accept', basename($new)))->assertRedirect($new);
        $this->assertDatabaseCount('event_members', 0);
    }

    public function test_expired_invitation_and_removed_inviter_cannot_grant_access(): void
    {
        $this->freezeTime();
        $admin = EventMember::factory()->create(['role' => EventRole::Admin]);
        $recipient = User::factory()->create();
        $invitation = EventInvitation::factory()->for($admin->event)->create([
            'invited_by' => $admin->user_id, 'email' => $recipient->email,
            'token_hash' => hash('sha256', 'expired'), 'expires_at' => now()->subSecond(),
        ]);
        $this->actingAs($recipient)->post(route('invitations.accept', 'expired'))->assertRedirect();
        $this->assertDatabaseCount('event_members', 1);
        $invitation->update(['expires_at' => now()->addDay()]);
        $admin->delete();
        $this->get(route('invitations.show', 'expired'))->assertInertia(fn (Assert $page) => $page->where('invitation', null));
        $this->post(route('invitations.accept', 'expired'))->assertRedirect();
        $this->assertDatabaseCount('event_members', 0);
    }

    public function test_admin_cannot_invite_admins_or_rewrite_owner_admin_invitation(): void
    {
        Mail::fake();
        $admin = EventMember::factory()->create(['role' => EventRole::Admin]);
        $invitation = EventInvitation::factory()->for($admin->event)->create(['role' => EventRole::Admin]);
        $this->actingAs($admin->user)->post(route('events.invitations.store', $admin->event), ['email' => 'planner@example.com', 'role' => 'admin'])->assertForbidden();
        $this->post(route('events.invitations.store', $admin->event), ['email' => $invitation->email, 'role' => 'viewer'])->assertForbidden();
        $this->delete(route('events.invitations.destroy', [$admin->event, $invitation]))->assertForbidden();
        Mail::assertNothingSent();
    }

    public function test_invitation_endpoints_reject_cross_event_records_and_existing_members(): void
    {
        Mail::fake();
        $member = EventMember::factory()->create();
        $foreign = EventInvitation::factory()->create();
        $this->actingAs($member->event->user)->delete(route('events.invitations.destroy', [$member->event, $foreign]))->assertNotFound();
        $this->post(route('events.invitations.store', $member->event), ['email' => $member->user->email, 'role' => 'editor'])->assertSessionHasErrors('email');
        $this->post(route('events.invitations.store', $member->event), ['email' => 'bad', 'role' => 'owner'])->assertSessionHasErrors(['email', 'role']);
        Mail::assertNothingSent();
    }

    public function test_failed_mail_delivery_rolls_back_invitation_and_returns_retry_message(): void
    {
        $event = Event::factory()->create();
        Mail::shouldReceive('to')->once()->with('recipient@example.com')->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new TransportException('Mail unavailable'));

        $this->actingAs($event->user)->post(route('events.invitations.store', $event), ['email' => 'recipient@example.com', 'role' => 'viewer'])
            ->assertSessionHasErrors(['email' => 'The invitation could not be sent. Please try again.']);
        $this->assertDatabaseCount('event_invitations', 0);
    }

    public function test_guest_cannot_accept_without_authentication_and_pending_recipient_has_no_access(): void
    {
        $recipient = User::factory()->create();
        $invitation = EventInvitation::factory()->create(['email' => $recipient->email, 'token_hash' => hash('sha256', 'pending')]);
        $this->post(route('invitations.accept', 'pending'))->assertRedirect(route('login'));
        $this->actingAs($recipient)->get(route('events.show', $invitation->event))->assertNotFound();
        $this->assertDatabaseCount('event_members', 0);
    }
}
