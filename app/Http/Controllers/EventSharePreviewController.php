<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventShare;
use App\SharedEventData;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventSharePreviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Event $event, SharedEventData $data): Response
    {
        Gate::authorize('view', $event);
        Gate::authorize('manageSharing', $event);

        return Inertia::render('Shared/Show', [
            'plan' => $data->forEvent($event, $event->share ?? new EventShare),
            'backUrl' => route('events.sharing.index', $event),
        ]);
    }
}
