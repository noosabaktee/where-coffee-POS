<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_and_delete_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $createResponse = $this->actingAs($admin)->postJson('/api/outlets', [
            'code' => 'BDG',
            'name' => 'Where Coffee Bandung',
            'address' => 'Jl. Braga No. 10, Bandung',
            'phone' => '022-1234567',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $createResponse->assertCreated()->assertJsonPath('data.code', 'BDG');
        $outletId = $createResponse->json('data.id');

        $this->actingAs($admin)->putJson('/api/outlets/'.$outletId, [
            'code' => 'BDG',
            'name' => 'Where Coffee Bandung Dago',
            'address' => 'Jl. Dago No. 21, Bandung',
            'phone' => '022-7654321',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ])->assertOk()->assertJsonPath('data.name', 'Where Coffee Bandung Dago');

        $this->actingAs($admin)->deleteJson('/api/outlets/'.$outletId)
            ->assertOk();

        $this->assertDatabaseMissing('outlets', ['id' => $outletId]);
    }

    public function test_non_admin_cannot_manage_outlets(): void
    {
        $this->seed(DatabaseSeeder::class);
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();

        $this->actingAs($cashier)->postJson('/api/outlets', [
            'code' => 'DENPASAR',
            'name' => 'Where Coffee Denpasar',
            'address' => 'Jl. Imam Bonjol No. 1',
            'phone' => '0361-123456',
            'timezone' => 'Asia/Makassar',
            'is_active' => true,
        ])->assertForbidden();
    }
}
