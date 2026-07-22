<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_modify_product_from_another_outlet(): void
    {
        $this->seed(DatabaseSeeder::class);
        $utama = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $selatan = Outlet::query()->where('code', 'SELATAN')->firstOrFail();
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();
        $foreignProduct = Product::query()->where('outlet_id', $selatan->id)->firstOrFail();
        $cashier->givePermissionTo('products.update');

        $this->actingAs($cashier)
            ->withSession(['current_outlet_id' => $utama->id])
            ->putJson('/api/products/'.$foreignProduct->id, ['name' => 'Tidak Boleh Diubah'])
            ->assertForbidden();
    }

    public function test_administrator_can_switch_outlet_context(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $selatan = Outlet::query()->where('code', 'SELATAN')->firstOrFail();

        $this->actingAs($admin)
            ->putJson('/api/context/outlet', ['outlet_id' => $selatan->id])
            ->assertOk()
            ->assertJsonPath('outlet.id', $selatan->id);

        $this->assertSame($selatan->id, session('current_outlet_id'));
    }
}
