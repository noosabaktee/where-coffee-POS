<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_administrator_can_login_with_session_authentication(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->postJson('/login', [
            'username' => 'admin',
            'password' => '123456',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.username', 'admin')
            ->assertJsonPath('user.role', 'Administrator')
            ->assertJsonPath('redirect', route('dashboard'));

        $this->assertAuthenticatedAs(User::query()->where('username', 'admin')->firstOrFail());
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->seed(DatabaseSeeder::class);
        User::query()->where('username', 'kasir1')->update(['is_active' => false]);

        $this->postJson('/login', [
            'username' => 'kasir1',
            'password' => '123456',
        ])->assertUnprocessable();

        $this->assertGuest();
    }
}
