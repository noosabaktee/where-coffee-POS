<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TransactionItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_with_transaction_history_is_deactivated_and_can_be_reactivated(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $product = Product::query()
            ->whereKey(TransactionItem::query()->whereNotNull('product_id')->value('product_id'))
            ->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['current_outlet_id' => $product->outlet_id])
            ->deleteJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('message', 'Produk memiliki riwayat transaksi dan telah dinonaktifkan.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->withSession(['current_outlet_id' => $product->outlet_id])
            ->putJson('/api/products/'.$product->id, ['is_active' => true])
            ->assertOk()
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => true,
        ]);
    }
}
