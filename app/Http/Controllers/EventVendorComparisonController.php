<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventVendor;
use App\VendorStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventVendorComparisonController extends Controller
{
    public function __invoke(Request $request, Event $event): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);
        $validated = $request->validate([
            'vendors' => ['required', 'array', 'min:2', 'max:4'],
            'vendors.*' => ['required', 'integer', 'distinct'],
        ]);
        $vendors = $event->vendors()->whereIn('id', $validated['vendors'])->get()->keyBy('id');
        abort_unless($vendors->count() === count($validated['vendors']), 404);

        return Inertia::render('Events/VendorComparison', [
            'event' => $event->only(['id', 'name']),
            'vendors' => collect($validated['vendors'])->map(fn (int|string $id): EventVendor => $vendors->get($id))->values(),
            'statuses' => collect(VendorStatus::cases())->mapWithKeys(fn (VendorStatus $status): array => [$status->value => $status->label()]),
            'canEdit' => $request->user()->can('editPlanning', $event),
        ]);
    }
}
