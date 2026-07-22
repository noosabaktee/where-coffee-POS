<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_server_prices_reduces_stock_and_records_audit_rows(): void
    {
        $this->seed(DatabaseSeeder::class);
        $outlet = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();
        $customer = Customer::query()->firstOrFail();
        $product = Product::query()->where('outlet_id', $outlet->id)->where('stock', '>', 5)->firstOrFail();
        $stockBefore = $product->stock;

        $response = $this->actingAs($cashier)
            ->withSession(['current_outlet_id' => $outlet->id])
            ->postJson('/api/transactions', [
                'items' => [['product_id' => $product->id, 'quantity' => 2, 'price' => 1]],
                'customer_id' => $customer->id,
                'discount_percentage' => 0,
                'payment_method' => 'QRIS',
                'amount_paid' => 0,
                'use_points' => false,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.items.0.price', (float) $product->selling_price)
            ->assertJsonPath('data.payment_method', 'QRIS');

        $transaction = Transaction::query()->latest('id')->firstOrFail();
        $this->assertSame($stockBefore - 2, $product->fresh()->stock);
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $transaction->id, 'product_id' => $product->id, 'quantity' => 2]);
        $this->assertDatabaseHas('stock_movements', ['transaction_id' => $transaction->id, 'product_id' => $product->id, 'quantity_change' => -2]);
    }

    public function test_checkout_rejects_quantity_above_available_stock(): void
    {
        $this->seed(DatabaseSeeder::class);
        $outlet = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $cashier = User::query()->where('username', 'kasir1')->firstOrFail();
        $product = Product::query()->where('outlet_id', $outlet->id)->firstOrFail();

        $this->actingAs($cashier)
            ->withSession(['current_outlet_id' => $outlet->id])
            ->postJson('/api/transactions', [
                'items' => [['product_id' => $product->id, 'quantity' => $product->stock + 1]],
                'discount_percentage' => 0,
                'payment_method' => 'Tunai',
                'amount_paid' => 99999999,
                'use_points' => false,
            ])->assertUnprocessable();
    }
}
