<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventExpense>
 */
class EventExpenseFactory extends Factory
{
    public function withActualCost(): static
    {
        return $this->state(fn (): array => ['actual_minor' => 100000]);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory()->withBudget(),
            'title' => fake()->words(3, true),
            'category' => fake()->randomElement(['Venue', 'Catering', 'Production']),
            'estimated_minor' => fake()->numberBetween(1000, 500000),
        ];
    }
}
