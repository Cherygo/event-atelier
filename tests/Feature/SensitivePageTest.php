<?php

namespace Tests\Feature;

use App\Models\EventInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SensitivePageTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('accountPages')]
    public function test_account_pages_disable_caching_referrers_and_indexing(string $path): void
    {
        $response = $this->get($path)->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public static function accountPages(): array
    {
        return [
            'login' => ['/login'],
            'register' => ['/register'],
            'request reset' => ['/forgot-password'],
            'reset token' => ['/reset-password/example-token?email=recipient@example.test'],
        ];
    }

    public function test_valid_and_revoked_invitation_pages_protect_the_bearer_link(): void
    {
        $invitation = EventInvitation::factory()->create(['token_hash' => hash('sha256', 'private-token')]);
        $url = route('invitations.show', 'private-token');
        $response = $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $invitation->delete();

        $response = $this->get($url)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
