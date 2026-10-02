<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountRateLimitTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('guestActions')]
    public function test_guest_account_actions_are_limited_per_route_and_recover_after_a_minute(string $path): void
    {
        $this->freezeTime();
        Notification::fake();

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post($path, [])->assertSessionHasErrors();
        }

        $this->post($path, [])->assertTooManyRequests()->assertHeader('Retry-After');
        $this->get('/login')->assertOk();
        $this->assertDatabaseCount('users', 0);
        Notification::assertNothingSent();

        $this->travel(61)->seconds();
        $this->post($path, [])->assertRedirect()->assertSessionHasErrors();
    }

    public static function guestActions(): array
    {
        return [
            'registration' => ['/register'],
            'recovery request' => ['/forgot-password'],
            'reset submission' => ['/reset-password'],
        ];
    }

    #[DataProvider('authenticatedActions')]
    public function test_password_checks_are_limited_per_user(string $method, string $path): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->{$method}($path, ['password' => 'incorrect'])->assertSessionHasErrors();
        }

        $this->{$method}($path, ['password' => 'incorrect'])->assertTooManyRequests();
        $this->actingAs($otherUser)->{$method}($path, ['password' => 'incorrect'])
            ->assertRedirect()->assertSessionHasErrors();
        $this->assertModelExists($user);
        $this->assertModelExists($otherUser);
    }

    public static function authenticatedActions(): array
    {
        return [
            'confirm password' => ['post', '/confirm-password'],
            'change password' => ['put', '/password'],
            'delete account' => ['delete', '/profile'],
        ];
    }
}
