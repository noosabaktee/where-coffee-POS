<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_selling_price_must_not_be_lower_than_cost_price(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $outlet = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $category = Category::query()->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['current_outlet_id' => $outlet->id])
            ->postJson('/api/products', [
                'category_id' => $category->id,
                'barcode' => 'TEST-LOW-PRICE',
                'name' => 'Invalid Product',
                'cost_price' => 20000,
                'selling_price' => 10000,
                'stock' => 10,
                'min_stock' => 5,
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('selling_price');
    }
}
