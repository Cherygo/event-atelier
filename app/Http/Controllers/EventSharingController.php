<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEventSharingRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventSharingController extends Controller
{
    public function index(Request $request, Event $event): Response
    {
        Gate::authorize('view', $event);
        $canManage = $request->user()->can('manageSharing', $event);
        $share = $canManage ? $event->share : null;

        return Inertia::render('Events/Sharing', [
            'event' => $event->only(['id', 'name']),
            'canManage' => $canManage,
            'settings' => $canManage ? [
                'show_date' => $share?->show_date ?? false,
                'show_location' => $share?->show_location ?? false,
                'public_note' => $share?->public_note ?? '',
                'show_progress' => $share?->show_progress ?? false,
                'show_vendors' => $share?->show_vendors ?? false,
                'show_budget' => $share?->show_budget ?? false,
            ] : null,
            'shareUrl' => $share?->token ? route('shared.show', $share->token) : null,
        ]);
    }

    public function update(UpdateEventSharingRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('manageSharing', $event);
            $event->share()->updateOrCreate([], $request->validated());
        });

        return to_route('events.sharing.index', $event)->with('status', 'Sharing choices saved. If your page is published, these changes are now visible to anyone with its link.');
    }

    public function store(Event $event): RedirectResponse
    {
        Gate::authorize('view', $event);
        Gate::authorize('manageSharing', $event);
        DB::transaction(function () use ($event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('manageSharing', $event);
            $share = $event->share()->firstOrCreate();
            if ($share->token_hash === null) {
                $share->publish();
            }
        });

        return to_route('events.sharing.index', $event)->with('status', 'Your shared page is published. Anyone with the link can read the selected information.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('view', $event);
        Gate::authorize('manageSharing', $event);
        DB::transaction(function () use ($event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('manageSharing', $event);
            $event->share?->revoke();
        });

        return to_route('events.sharing.index', $event)->with('status', 'Sharing stopped. The old link no longer opens this event. Copies already saved by visitors cannot be recalled.');
    }

    public function rotate(Event $event): RedirectResponse
    {
        Gate::authorize('view', $event);
        Gate::authorize('manageSharing', $event);
        DB::transaction(function () use ($event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('manageSharing', $event);
            $share = $event->share;
            abort_unless($share?->token_hash, 409, 'Publish the page before replacing its link.');
            $share->publish();
        });

        return to_route('events.sharing.index', $event)->with('status', 'Link replaced. The previous link no longer works. Send the new link to anyone who should keep access.');
    }
}
