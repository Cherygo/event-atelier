<?php

namespace App\Policies;

use App\EventRole;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function view(User $user, Event $event): Response
    {
        return $event->roleFor($user) !== null ? Response::allow() : Response::denyAsNotFound();
    }

    public function update(User $user, Event $event): bool
    {
        return in_array($event->roleFor($user), [EventRole::Owner, EventRole::Admin], true);
    }

    public function editPlanning(User $user, Event $event): bool
    {
        return in_array($event->roleFor($user), [EventRole::Owner, EventRole::Admin, EventRole::Editor], true);
    }

    public function delete(User $user, Event $event): bool
    {
        return $event->user_id === $user->id;
    }

    public function transferOwnership(User $user, Event $event): bool
    {
        return $this->delete($user, $event);
    }

    public function manageMembers(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function invite(User $user, Event $event, string $role): bool
    {
        if (! in_array($role, ['admin', 'editor', 'viewer'], true)) {
            return false;
        }

        return $event->user_id === $user->id
            || ($event->roleFor($user) === EventRole::Admin && $role !== 'admin');
    }

    public function manageMember(User $user, Event $event, EventMember $member): bool
    {
        return $member->event_id === $event->id
            && $member->user_id !== $user->id
            && $member->user_id !== $event->user_id
            && $this->invite($user, $event, $member->role->value);
    }
}
