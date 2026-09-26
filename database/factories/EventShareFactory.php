<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventShare;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventShare>
 */
class EventShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'show_date' => false,
            'show_location' => false,
            'public_note' => null,
        ];
    }
}
