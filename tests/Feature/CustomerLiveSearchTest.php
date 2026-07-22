<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerLiveSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_live_search_active_members_by_name(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();

        $this->actingAs($cashier)
            ->getJson('/api/customers/search?q=Nadia')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'Nadia Putri',
            ]);
    }

    public function test_pos_bootstrap_does_not_preload_the_full_customer_directory(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();

        $this->actingAs($cashier)
            ->getJson('/api/bootstrap?page=pos')
            ->assertOk()
            ->assertJsonCount(0, 'customers');
    }

    public function test_live_search_requires_at_least_two_characters(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();

        $this->actingAs($cashier)
            ->getJson('/api/customers/search?q=N')
            ->assertOk()
            ->assertExactJson([]);
    }
}
