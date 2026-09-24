<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEventVendorRequest;
use App\Models\Event;
use App\Models\EventVendor;
use App\VendorStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EventVendorController extends Controller
{
    public function index(Request $request, Event $event): Response
    {
        $event->load('members');
        Gate::authorize('view', $event);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:60'],
            'page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['all', ...array_column(VendorStatus::cases(), 'value')])],
        ]);
        $vendors = $event->vendors();
        $status = $filters['status'] ?? 'all';
        if ($status !== 'all') {
            $vendors->where('status', $status);
        }
        if (! empty($filters['search'])) {
            $vendors->whereLike('name', '%'.$filters['search'].'%');
        }
        if (! empty($filters['category'])) {
            $vendors->where('category', $filters['category']);
        }

        return Inertia::render('Events/Vendors', [
            'event' => $event->only(['id', 'name']),
            'vendors' => $vendors->latest('id')->paginate(12)->withQueryString(),
            'categories' => $event->vendors()->distinct()->orderBy('category')->pluck('category'),
            'filters' => ['search' => $filters['search'] ?? '', 'category' => $filters['category'] ?? '', 'status' => $status],
            'statuses' => collect(VendorStatus::cases())->mapWithKeys(fn (VendorStatus $status): array => [$status->value => $status->label()]),
            'currencies' => EventVendor::CURRENCIES,
            'canEdit' => $request->user()->can('editPlanning', $event),
        ]);
    }

    public function store(SaveEventVendorRequest $request, Event $event): RedirectResponse
    {
        DB::transaction(function () use ($request, $event): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->vendors()->create($request->validated());
        });

        return to_route('events.vendors.index', $event)->with('status', 'Vendor added.');
    }

    public function update(SaveEventVendorRequest $request, Event $event, EventVendor $vendor): RedirectResponse
    {
        DB::transaction(function () use ($request, $event, $vendor): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->vendors()->findOrFail($vendor->id)->update($request->validated());
        });

        return back()->with('status', 'Vendor saved.');
    }

    public function destroy(Event $event, EventVendor $vendor): RedirectResponse
    {
        Gate::authorize('view', $event);
        DB::transaction(function () use ($event, $vendor): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::authorize('editPlanning', $event);
            $event->vendors()->findOrFail($vendor->id)->delete();
        });

        return to_route('events.vendors.index', $event)->with('status', 'Vendor deleted.');
    }
}
