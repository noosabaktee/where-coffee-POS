<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(20260721);
        $transactionCount = app()->environment('testing') ? 24 : 240;
        $historyDays = app()->environment('testing') ? 44 : 239;
        $customers = Customer::query()->get();

        foreach (Outlet::query()->get() as $outletIndex => $outlet) {
            $cashiers = User::query()->where('outlet_id', $outlet->id)->get();
            $products = Product::query()->with('category')->where('outlet_id', $outlet->id)->get();

            foreach (range(1, $transactionCount) as $sequence) {
                $daysAgo = mt_rand(0, $historyDays);
                $hour = mt_rand(8, 21);
                $minute = mt_rand(0, 59);
                $transactedAt = now($outlet->timezone)->subDays($daysAgo)->setTime($hour, $minute);
                $lineCount = mt_rand(1, 4);
                $selected = $products->shuffle()->take($lineCount);
                $subtotal = 0.0;
                $costTotal = 0.0;
                $lineRows = [];

                foreach ($selected as $product) {
                    $quantity = mt_rand(1, 2);
                    if ($product->stock < $quantity + 2) {
                        continue;
                    }
                    $lineSubtotal = (float) $product->selling_price * $quantity;
                    $lineCost = (float) $product->cost_price * $quantity;
                    $subtotal += $lineSubtotal;
                    $costTotal += $lineCost;
                    $lineRows[] = compact('product', 'quantity', 'lineSubtotal', 'lineCost');
                }

                if ($lineRows === []) {
                    continue;
                }

                $discountPercentage = $sequence % 11 === 0 ? 10 : ($sequence % 7 === 0 ? 5 : 0);
                $discountAmount = round($subtotal * ($discountPercentage / 100), 2);
                $afterDiscount = $subtotal - $discountAmount;
                $serviceRate = $outlet->code === 'UTAMA' ? 5 : 0;
                $serviceAmount = round($afterDiscount * ($serviceRate / 100), 2);
                $taxAmount = round(($afterDiscount + $serviceAmount) * 0.10, 2);
                $grandTotal = $afterDiscount + $serviceAmount + $taxAmount;
                $customer = $sequence % 3 === 0 ? $customers[($sequence + $outletIndex) % $customers->count()] : null;
                $cashier = $cashiers[($sequence - 1) % max(1, $cashiers->count())];
                $payment = ['Tunai', 'QRIS', 'Debit'][$sequence % 3];
                $amountPaid = $payment === 'Tunai' ? ceil($grandTotal / 10000) * 10000 : $grandTotal;

                $transaction = Transaction::query()->updateOrCreate([
                    'invoice_number' => sprintf('WC-%s-%s-%03d', $outlet->code, $transactedAt->format('ymd'), $sequence),
                ], [
                    'outlet_id' => $outlet->id,
                    'user_id' => $cashier?->id,
                    'customer_id' => $customer?->id,
                    'transacted_at' => $transactedAt,
                    'status' => 'paid',
                    'payment_method' => $payment,
                    'subtotal' => $subtotal,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'service_charge_percentage' => $serviceRate,
                    'service_charge_amount' => $serviceAmount,
                    'tax_percentage' => 10,
                    'tax_amount' => $taxAmount,
                    'grand_total' => $grandTotal,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $amountPaid - $grandTotal,
                    'cost_total' => $costTotal,
                    'gross_profit' => max(0, ($subtotal - $discountAmount) - $costTotal),
                ]);

                if ($transaction->items()->exists()) {
                    continue;
                }

                foreach ($lineRows as $line) {
                    $product = $line['product'];
                    $before = $product->stock;
                    $after = $before - $line['quantity'];
                    $transaction->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'category_name' => $product->category->name,
                        'unit_cost' => $product->cost_price,
                        'unit_price' => $product->selling_price,
                        'quantity' => $line['quantity'],
                        'line_subtotal' => $line['lineSubtotal'],
                        'line_cost' => $line['lineCost'],
                        'line_profit' => $line['lineSubtotal'] - $line['lineCost'],
                    ]);
                    $product->update(['stock' => $after]);
                    StockMovement::query()->create([
                        'outlet_id' => $outlet->id,
                        'product_id' => $product->id,
                        'user_id' => $cashier?->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'sale',
                        'quantity_change' => -$line['quantity'],
                        'stock_before' => $before,
                        'stock_after' => $after,
                        'reference' => $transaction->invoice_number,
                        'notes' => 'Penjualan historis dari seeder demo',
                        'created_at' => $transactedAt,
                        'updated_at' => $transactedAt,
                    ]);
                }

                if ($customer) {
                    $earned = (int) floor($grandTotal / 10000);
                    if ($earned > 0) {
                        $customer->increment('points', $earned);
                        $customer->refresh();
                        LoyaltyTransaction::query()->create([
                            'customer_id' => $customer->id,
                            'transaction_id' => $transaction->id,
                            'type' => 'earn',
                            'points_change' => $earned,
                            'balance_after' => $customer->points,
                            'notes' => 'Poin transaksi demo',
                            'created_at' => $transactedAt,
                            'updated_at' => $transactedAt,
                        ]);
                        $customer->update(['last_visit_at' => $transactedAt]);
                    }
                }
            }
        }
    }
}
