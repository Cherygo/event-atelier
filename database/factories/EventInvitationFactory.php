<?php

namespace Database\Factories;

use App\EventRole;
use App\Models\Event;
use App\Models\EventInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<EventInvitation> */
class EventInvitationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'invited_by' => fn (array $attributes): int => Event::findOrFail($attributes['event_id'])->user_id,
            'email' => fake()->unique()->safeEmail(),
            'role' => EventRole::Viewer,
            'token_hash' => hash('sha256', Str::random(64)),
            'expires_at' => now()->addDays(7),
        ];
    }
}
