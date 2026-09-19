<?php

namespace App\Http\Controllers;

use App\EventRole;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EventOwnershipController extends Controller
{
    public function update(Request $request, Event $event): RedirectResponse
    {
        Gate::authorize('view', $event);
        Gate::authorize('transferOwnership', $event);
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'password' => ['required', 'current_password'],
        ]);

        DB::transaction(function () use ($event, $validated): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('transferOwnership', $event);
            $member = $event->members()->findOrFail($validated['member_id']);
            $previousOwner = $event->user_id;
            $event->user_id = $member->user_id;
            $event->save();
            $member->delete();
            $event->members()->create(['user_id' => $previousOwner, 'role' => EventRole::Admin]);
            $event->invitations()->delete();
        });

        return back()->with('status', 'Ownership transferred. You are now a planner / admin. Pending invitations have been revoked.');
    }
}
