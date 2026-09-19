<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteEventMemberRequest;
use App\Mail\EventInvitationMail;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EventInvitationController extends Controller
{
    public function store(InviteEventMemberRequest $request, Event $event): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $event): void {
                $event = Event::query()->lockForUpdate()->findOrFail($event->id);
                Gate::authorize('invite', [$event, $request->validated('role')]);
                $email = $request->validated('email');
                $recipient = User::whereRaw('LOWER(email) = ?', [$email])->first();

                if ($recipient && $event->roleFor($recipient) !== null) {
                    throw ValidationException::withMessages(['email' => 'This person already has access to this event.']);
                }

                $existing = $event->invitations()->where('email', $email)->first();
                if ($existing) {
                    Gate::authorize('invite', [$event, $existing->role->value]);
                }

                $token = Str::random(64);
                $invitation = $event->invitations()->updateOrCreate(['email' => $email], [
                    'role' => $request->validated('role'),
                    'invited_by' => $request->user()->id,
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => now()->addDays(7),
                ]);

                Mail::to($email)->send(new EventInvitationMail($invitation, route('invitations.show', $token)));
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withErrors(['email' => 'The invitation could not be sent. Please try again.']);
        }

        return back()->with('status', 'Invitation sent. The email link is valid for seven days.');
    }

    public function destroy(Event $event, EventInvitation $invitation): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $invitation): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            $invitation = $event->invitations()->findOrFail($invitation->id);
            Gate::authorize('invite', [$event, $invitation->role->value]);
            $invitation->delete();
        });

        return back()->with('status', 'Invitation revoked.');
    }
}
