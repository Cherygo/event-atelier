<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EventTask> */
class EventTaskFactory extends Factory
{
    public function definition(): array
    {
        return ['event_id' => Event::factory(), 'title' => fake()->sentence(4), 'notes' => null, 'category' => null];
    }
}
