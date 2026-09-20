<?php

namespace App\Http\Controllers;

use App\EventRole;
use App\Http\Requests\UpdateEventMemberRequest;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\EventMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventMemberController extends Controller
{
    public function index(Request $request, Event $event): Response
    {
        Gate::authorize('view', $event);
        $event->load(['user', 'members.user']);
        $canManage = $request->user()->can('manageMembers', $event);

        return Inertia::render('Events/People', [
            'event' => $event->only(['id', 'name']),
            'owner' => ['id' => $event->user->id, 'name' => $event->user->name, 'email' => $canManage ? $event->user->email : null],
            'members' => $event->members->map(fn (EventMember $member): array => [
                'id' => $member->id,
                'name' => $member->user->name,
                'email' => $canManage ? $member->user->email : null,
                'role' => $member->role->value,
                'canManage' => $request->user()->can('manageMember', [$event, $member]),
            ]),
            'invitations' => $canManage ? $event->invitations()->with('inviter')->latest()->get()->map(fn (EventInvitation $invitation): array => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'expired' => ! $invitation->setRelation('event', $event)->isUsable(),
                'canManage' => $request->user()->can('invite', [$event, $invitation->role->value]),
            ]) : [],
            'roles' => collect([EventRole::Admin, EventRole::Editor, EventRole::Viewer])
                ->filter(fn (EventRole $role): bool => $request->user()->can('invite', [$event, $role->value]))
                ->map(fn (EventRole $role): array => ['value' => $role->value, 'label' => $role->label()])->values(),
            'can' => [
                'manageMembers' => $canManage,
                'transferOwnership' => $request->user()->can('transferOwnership', $event),
            ],
        ]);
    }

    public function update(UpdateEventMemberRequest $request, Event $event, EventMember $member): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $member): void {
            $locked = Event::query()->lockForUpdate()->findOrFail($event->id);
            $member = $locked->members()->findOrFail($member->id);
            Gate::authorize('manageMember', [$locked, $member]);
            Gate::authorize('invite', [$locked, $request->validated('role')]);
            $member->update($request->validated());
            if ($member->role === EventRole::Viewer) {
                $locked->tasks()->where('assigned_to', $member->user_id)->update(['assigned_to' => null]);
            }
        });

        return back()->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Event $event, EventMember $member): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $member): void {
            $locked = Event::query()->lockForUpdate()->findOrFail($event->id);
            $member = $locked->members()->findOrFail($member->id);
            Gate::authorize('manageMember', [$locked, $member]);
            $locked->tasks()->where('assigned_to', $member->user_id)->update(['assigned_to' => null]);
            $member->delete();
        });

        return back()->with('status', 'Access removed.');
    }
}
