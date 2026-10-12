<?php

namespace Tests\Feature;

use App\Models\EventShare;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_sharing_is_private_by_default_and_tokens_are_protected(): void
    {
        $share = EventShare::factory()->create();
        $this->assertNull($share->token);
        $this->assertFalse($share->show_date);
        $this->assertFalse($share->show_location);
        $share->publish();
        $share->refresh();
        $this->assertSame(64, strlen($share->token));
        $this->assertSame(hash('sha256', $share->token), $share->token_hash);
        $this->assertNotSame($share->token, $share->getRawOriginal('token'));
        $this->assertArrayNotHasKey('token', $share->toArray());
        $this->assertArrayNotHasKey('token_hash', $share->toArray());
    }

    public function test_deleting_an_event_removes_its_share_link(): void
    {
        $share = EventShare::factory()->create();
        $share->publish();
        $share->event->delete();
        $this->assertDatabaseMissing('event_shares', ['id' => $share->id]);
    }
}
