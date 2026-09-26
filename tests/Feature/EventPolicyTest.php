<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EventPolicyTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [
            'owner' => ['owner', true, true, true, true],
            'admin' => ['admin', true, true, true, false],
            'editor' => ['editor', true, false, true, false],
            'viewer' => ['viewer', true, false, false, false],
            'outsider' => [null, false, false, false, false],
        ];
    }

    #[DataProvider('roles')]
    public function test_event_permission_matrix(?string $role, bool $view, bool $manage, bool $edit, bool $own): void
    {
        $event = Event::factory()->create();
        $user = $role === 'owner' ? $event->user : User::factory()->create();
        if ($role !== null && $role !== 'owner') {
            EventMember::factory()->for($event)->for($user)->create(['role' => $role]);
        }

        $this->assertSame($view, $user->can('view', $event));
        $this->assertSame($manage, $user->can('update', $event));
        $this->assertSame($manage, $user->can('manageMembers', $event));
        $this->assertSame($edit, $user->can('editPlanning', $event));
        $this->assertSame($own, $user->can('delete', $event));
        $this->assertSame($own, $user->can('transferOwnership', $event));
        $this->assertSame($own, $user->can('manageSharing', $event));
        $this->assertSame($own, $user->can('invite', [$event, 'admin']));
        $this->assertSame($manage, $user->can('invite', [$event, 'editor']));
        $this->assertSame($manage, $user->can('invite', [$event, 'viewer']));
        $this->assertFalse($user->can('invite', [$event, 'owner']));
    }
}
