<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventVendor> */
class EventVendorFactory extends Factory
{
    public function definition(): array
    {
        return ['event_id' => Event::factory(), 'name' => fake()->company(), 'category' => 'Venue'];
    }
}
