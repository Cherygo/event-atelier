<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EventVendorComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_comparison_preserves_selection_order_and_original_currencies(): void
    {
        $event = Event::factory()->create();
        $one = EventVendor::factory()->for($event)->create(['quote_amount' => '900', 'currency' => 'EUR', 'notes' => 'Includes chairs']);
        $two = EventVendor::factory()->for($event)->create(['quote_amount' => '800', 'currency' => 'GBP']);
        $this->actingAs($event->user)->get(route('events.vendors.compare', ['event' => $event, 'vendors' => [$two->id, $one->id]]))
            ->assertInertia(fn (Assert $page) => $page->component('Events/VendorComparison')->has('vendors', 2)
                ->where('vendors.0.id', $two->id)->where('vendors.0.currency', 'GBP')->where('vendors.1.quote_amount', '900.00')->where('vendors.1.notes', 'Includes chairs'));
    }

    public function test_comparison_requires_access_to_every_selected_vendor(): void
    {
        $event = Event::factory()->create();
        $own = EventVendor::factory()->for($event)->create();
        $foreign = EventVendor::factory()->create();
        $url = route('events.vendors.compare', ['event' => $event, 'vendors' => [$own->id, $foreign->id]]);
        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($event->user)->get($url)->assertNotFound();
        $this->get(route('events.vendors.compare', ['event' => $foreign->event, 'vendors' => [$own->id, $foreign->id]]))->assertNotFound();
    }

    public function test_viewer_can_compare_without_edit_controls(): void
    {
        $member = EventMember::factory()->create(['role' => 'viewer']);
        $vendors = EventVendor::factory()->for($member->event)->count(2)->create();
        $this->actingAs($member->user)->get(route('events.vendors.compare', ['event' => $member->event, 'vendors' => $vendors->modelKeys()]))
            ->assertInertia(fn (Assert $page) => $page->where('canEdit', false)->has('vendors', 2));
    }

    public function test_comparison_rejects_missing_duplicate_or_excessive_selections(): void
    {
        $event = Event::factory()->create();
        $vendors = EventVendor::factory()->for($event)->count(5)->create();
        $this->actingAs($event->user);
        foreach ([[], [$vendors[0]->id], $vendors->modelKeys()] as $ids) {
            $this->get(route('events.vendors.compare', ['event' => $event, 'vendors' => $ids]))->assertSessionHasErrors('vendors');
        }
        $this->get(route('events.vendors.compare', ['event' => $event, 'vendors' => [$vendors[0]->id, $vendors[0]->id]]))->assertSessionHasErrors('vendors.0');
    }
}
