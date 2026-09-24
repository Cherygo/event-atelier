<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\EventVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventVendorTest extends TestCase
{
    use RefreshDatabase;

    public static function editableRoles(): array
    {
        return [['owner'], ['admin'], ['editor']];
    }

    #[DataProvider('editableRoles')]
    public function test_collaborators_can_manage_event_vendors(string $role): void
    {
        $event = Event::factory()->create();
        $user = $role === 'owner' ? $event->user : EventMember::factory()->for($event)->create(['role' => $role])->user;
        $other = Event::factory()->create();
        $this->actingAs($user)->post(route('events.vendors.store', $event), [
            'name' => 'Garden House', 'category' => 'Venue', 'contact_name' => 'Alex', 'email' => 'alex@example.test',
            'phone' => '+44 1234 567890', 'website' => 'https://example.com', 'notes' => 'Step-free access', 'event_id' => $other->id,
        ])->assertRedirect(route('events.vendors.index', $event))->assertSessionHasNoErrors();
        $vendor = $event->vendors()->sole();
        $this->assertSame('Garden House', $vendor->name);
        $this->assertSame($event->id, $vendor->event_id);
        $this->patch(route('events.vendors.update', [$event, $vendor]), ['name' => 'Garden House updated', 'category' => 'Venue', 'notes' => null])->assertSessionHasNoErrors();
        $this->assertSame('Garden House updated', $vendor->fresh()->name);
        $this->assertNull($vendor->fresh()->notes);
        $this->delete(route('events.vendors.destroy', [$event, $vendor]))->assertRedirect();
        $this->assertModelMissing($vendor);
    }

    public function test_viewers_can_read_but_cannot_modify_vendors(): void
    {
        $member = EventMember::factory()->create(['role' => 'viewer']);
        $vendor = EventVendor::factory()->for($member->event)->create();
        $this->actingAs($member->user)->get(route('events.vendors.index', $member->event))->assertInertia(fn (Assert $page) => $page->where('canEdit', false)->where('vendors.data.0.id', $vendor->id));
        $this->post(route('events.vendors.store', $member->event), ['name' => 'No', 'category' => 'Venue'])->assertForbidden();
        $this->patch(route('events.vendors.update', [$member->event, $vendor]), ['name' => 'No', 'category' => 'Venue'])->assertForbidden();
        $this->delete(route('events.vendors.destroy', [$member->event, $vendor]))->assertForbidden();
        $this->assertModelExists($vendor);
    }

    public function test_vendors_are_isolated_and_require_authentication(): void
    {
        $event = Event::factory()->create();
        $foreign = EventVendor::factory()->create();
        $this->get(route('events.vendors.index', $event))->assertRedirect(route('login'));
        $this->post(route('events.vendors.store', $event), [])->assertRedirect(route('login'));
        $this->actingAs($event->user)->get(route('events.vendors.index', $foreign->event))->assertNotFound();
        $this->post(route('events.vendors.store', $foreign->event), ['name' => 'No', 'category' => 'Venue'])->assertNotFound();
        $this->patch(route('events.vendors.update', [$event, $foreign]), ['name' => 'No', 'category' => 'Venue'])->assertNotFound();
        $this->delete(route('events.vendors.destroy', [$event, $foreign]))->assertNotFound();
    }

    public function test_validation_rejects_unsafe_urls_and_invalid_contact_details(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->post(route('events.vendors.store', $event), [
            'name' => '', 'category' => '', 'email' => 'not-an-email', 'website' => 'javascript:alert(1)',
            'notes' => str_repeat('a', 5001), 'phone' => str_repeat('a', 61), 'contact_name' => str_repeat('a', 121),
        ])->assertSessionHasErrors(['name', 'category', 'email', 'website', 'notes', 'phone', 'contact_name']);
        $this->assertSame(0, $event->vendors()->count());
    }

    public function test_search_category_and_pagination_only_include_current_event(): void
    {
        $event = Event::factory()->create();
        EventVendor::factory()->for($event)->count(14)->create(['name' => 'Garden House', 'category' => 'Venue']);
        EventVendor::factory()->for($event)->create(['name' => 'Garden florist', 'category' => 'Flowers']);
        EventVendor::factory()->create(['name' => 'Garden Other', 'category' => 'Private category']);
        $this->actingAs($event->user)->get(route('events.vendors.index', ['event' => $event, 'search' => 'garden', 'category' => 'Venue', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->where('vendors.total', 14)->has('vendors.data', 2)->where('categories', ['Flowers', 'Venue']));
        $this->get(route('events.vendors.index', ['event' => $event, 'search' => "%' OR 1=1 --"]))->assertInertia(fn (Assert $page) => $page->where('vendors.total',0));
    }
}
