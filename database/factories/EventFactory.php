<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function withBudget(): static
    {
        return $this->state(fn (): array => ['budget_currency' => 'EUR', 'budget_target_minor' => 2500000]);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'type' => fake()->randomElement(['wedding', 'corporate', 'private', 'conference']),
            'event_date' => fake()->optional()->dateTimeBetween('+1 month', '+18 months'),
            'guest_count' => fake()->optional()->numberBetween(20, 300),
            'location' => fake()->optional()->city(),
        ];
    }
}
