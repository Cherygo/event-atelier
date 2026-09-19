<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AcceptEventInvitationController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $invitation = EventInvitation::where('token_hash', hash('sha256', $token))->first();
        $usable = $invitation?->isUsable() ?? false;
        if ($usable && ! $request->user()) {
            $request->session()->put('url.intended', route('invitations.show', $token));
        }

        return Inertia::render('Invitations/Show', [
            'invitation' => $usable ? [
                'event_name' => $invitation->event->name,
                'email' => $invitation->email,
                'role' => $invitation->role->label(),
                'expires_at' => $invitation->expires_at->toIso8601String(),
            ] : null,
            'acceptUrl' => $usable ? route('invitations.accept', $token) : null,
            'matchesEmail' => $usable && $request->user()
                && strtolower($request->user()->email) === $invitation->email,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = EventInvitation::where('token_hash', hash('sha256', $token))->first();
        if (! $invitation) {
            return to_route('invitations.show', $token);
        }

        return DB::transaction(function () use ($request, $token, $invitation): RedirectResponse {
            $event = Event::query()->lockForUpdate()->findOrFail($invitation->event_id);
            $invitation = $event->invitations()->where('token_hash', hash('sha256', $token))->first();
            if (! $invitation || ! $invitation->isUsable()) {
                return to_route('invitations.show', $token);
            }

            if (strtolower($request->user()->email) !== $invitation->email) {
                return back()->withErrors(['invitation' => 'Sign in with the email address this invitation was sent to.']);
            }

            if ($event->user_id !== $request->user()->id) {
                $event->members()->firstOrCreate(['user_id' => $request->user()->id], ['role' => $invitation->role]);
            }
            $invitation->delete();
            $request->session()->forget('url.intended');

            return to_route('events.show', $event)->with('status', 'You have joined the event.');
        });
    }
}
