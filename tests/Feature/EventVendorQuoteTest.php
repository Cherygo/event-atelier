<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventVendor;
use App\VendorStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventVendorQuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_updates_cannot_remove_currency_from_an_existing_quote(): void
    {
        $event = Event::factory()->create();
        $vendor = EventVendor::factory()->for($event)->create(['quote_amount' => '1200.50', 'currency' => 'EUR']);
        $this->actingAs($event->user)->patch(route('events.vendors.update', [$event, $vendor]), [
            'name' => $vendor->name, 'category' => $vendor->category, 'currency' => null,
        ])->assertSessionHasErrors('quote_amount');
        $this->assertSame('EUR', $vendor->fresh()->currency);
        $this->assertSame('1200.50', $vendor->fresh()->quote_amount);
    }

    public function test_quotes_preserve_cents_distinguish_zero_from_unknown_and_can_be_cleared(): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->post(route('events.vendors.store', $event), [
            'name' => 'The Garden', 'category' => 'Venue', 'status' => 'shortlisted', 'quote_amount' => '1234.56', 'currency' => 'EUR', 'quote_details' => 'Includes setup',
        ])->assertSessionHasNoErrors();
        $vendor = $event->vendors()->sole();
        $this->assertSame('1234.56', $vendor->quote_amount);
        $this->assertSame(VendorStatus::Shortlisted, $vendor->status);
        $this->get(route('events.vendors.index', $event))->assertInertia(fn (Assert $page) => $page->where('vendors.data.0.quote_amount', '1234.56')->where('vendors.data.0.currency', 'EUR'));
        $this->patch(route('events.vendors.update', [$event, $vendor]), ['name' => $vendor->name, 'category' => 'Venue', 'quote_amount' => '0', 'currency' => 'EUR', 'status' => 'booked'])->assertSessionHasNoErrors();
        $this->assertSame('0.00', $vendor->fresh()->quote_amount);
        $this->assertSame(VendorStatus::Booked, $vendor->fresh()->status);
        $this->patch(route('events.vendors.update', [$event, $vendor]), ['name' => $vendor->name, 'category' => 'Venue', 'quote_amount' => null, 'currency' => null, 'status' => 'researching'])->assertSessionHasNoErrors();
        $this->assertNull($vendor->fresh()->quote_amount);
    }

    public static function invalidQuotes(): array
    {
        return [
            'negative' => [['quote_amount' => '-1', 'currency' => 'EUR'], 'quote_amount'],
            'extra precision' => [['quote_amount' => '12.345', 'currency' => 'EUR'], 'quote_amount'],
            'too large' => [['quote_amount' => '10000000000', 'currency' => 'EUR'], 'quote_amount'],
            'missing currency' => [['quote_amount' => '10'], 'currency'],
            'unknown currency' => [['quote_amount' => '10', 'currency' => 'BAD'], 'currency'],
            'unknown status' => [['status' => 'paid'], 'status'],
            'long details' => [['quote_details' => str_repeat('x', 2001)], 'quote_details'],
        ];
    }

    #[DataProvider('invalidQuotes')]
    public function test_invalid_decision_data_is_rejected(array $data, string $field): void
    {
        $event = Event::factory()->create();
        $this->actingAs($event->user)->post(route('events.vendors.store', $event), array_merge(['name' => 'Venue', 'category' => 'Venue'], $data))->assertSessionHasErrors($field);
        $this->assertSame(0, $event->vendors()->count());
    }

    public function test_status_filter_is_event_scoped(): void
    {
        $event = Event::factory()->create();
        $booked = EventVendor::factory()->for($event)->create(['status' => 'booked']);
        EventVendor::factory()->for($event)->create(['status' => 'shortlisted']);
        EventVendor::factory()->create(['status' => 'booked']);
        $this->actingAs($event->user)->get(route('events.vendors.index', ['event' => $event, 'status' => 'booked']))
            ->assertInertia(fn (Assert $page) => $page->where('vendors.total', 1)->where('vendors.data.0.id', $booked->id)->where('filters.status', 'booked'));
    }
}
