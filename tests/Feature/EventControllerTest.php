<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_events(): void
    {
        $this->get('/events')->assertRedirect('/login');
    }

    public function test_user_only_sees_their_own_events(): void
    {
        $user = User::factory()->create();
        $ownEvent = Event::factory()->for($user)->create();
        $otherEvent = Event::factory()->create();

        $this->actingAs($user)
            ->get('/events')
            ->assertOk()
            ->assertSee($ownEvent->name)
            ->assertDontSee($otherEvent->name);
    }

    public function test_authenticated_user_can_create_an_event(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/events', [
            'name' => 'Maya & Adrian’s weekend',
            'type' => 'wedding',
            'event_date' => '2027-05-22 00:00:00',
            'guest_count' => 86,
            'location' => 'Ravello, Italy',
        ]);

        $response->assertRedirect('/events');

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'name' => 'Maya & Adrian’s weekend',
            'type' => 'wedding',
            'event_date' => '2027-05-22 00:00:00',
            'guest_count' => 86,
            'location' => 'Ravello, Italy',
        ]);
    }

    public function test_event_creation_requires_a_name_and_supported_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/events/create')
            ->post('/events', ['type' => 'festival']);

        $response
            ->assertSessionHasErrors(['name', 'type'])
            ->assertRedirect('/events/create');

        $this->assertDatabaseCount('events', 0);
    }
}
