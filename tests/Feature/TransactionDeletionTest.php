<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_delete_transaction(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $outlet = Outlet::query()->firstOrFail();

        $transaction = Transaction::create([
            'outlet_id' => $outlet->id,
            'user_id' => $admin->id,
            'customer_id' => null,
            'invoice_number' => 'INV-TEST-001',
            'transacted_at' => now(),
            'status' => 'paid',
            'payment_method' => 'cash',
            'subtotal' => 10000,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'service_charge_percentage' => 0,
            'service_charge_amount' => 0,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'points_redeemed' => 0,
            'points_discount_amount' => 0,
            'grand_total' => 10000,
            'amount_paid' => 10000,
            'change_amount' => 0,
            'cost_total' => 0,
            'gross_profit' => 0,
            'notes' => null,
        ]);

        $this->actingAs($admin)
            ->deleteJson('/api/transactions/' . $transaction->id)
            ->assertOk()
            ->assertJsonPath('message', 'Transaksi berhasil dihapus.');

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
