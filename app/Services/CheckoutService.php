<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LoyaltyTransaction;
use App\Models\Outlet;
use App\Models\OutletSetting;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly ReferenceNumberService $references)
    {
    }

    /** @param array<string, mixed> $payload */
    public function checkout(User $user, Outlet $outlet, array $payload): Transaction
    {
        return DB::transaction(function () use ($user, $outlet, $payload): Transaction {
            $settings = OutletSetting::query()->firstOrCreate(
                ['outlet_id' => $outlet->id],
                [
                    'store_name' => $outlet->name,
                    'address' => $outlet->address,
                    'phone' => $outlet->phone,
                    'timezone' => $outlet->timezone,
                ],
            );

            $requested = collect($payload['items'])->keyBy(fn (array $item) => (int) $item['product_id']);
            $products = Product::query()
                ->with('category')
                ->forOutlet($outlet)
                ->whereIn('id', $requested->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $requested->count()) {
                throw ValidationException::withMessages(['items' => 'Sebagian produk tidak ditemukan pada outlet aktif.']);
            }

            $subtotal = 0.0;
            $costTotal = 0.0;
            $lines = [];

            foreach ($requested as $productId => $item) {
                /** @var Product $product */
                $product = $products->get($productId);
                $quantity = (int) $item['quantity'];

                if (! $product->is_active) {
                    throw ValidationException::withMessages(['items' => "Produk {$product->name} sedang tidak aktif."]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} hanya tersisa {$product->stock}.",
                    ]);
                }

                $unitPrice = (float) $product->selling_price;
                $unitCost = (float) $product->cost_price;
                $lineSubtotal = round($unitPrice * $quantity, 2);
                $lineCost = round($unitCost * $quantity, 2);

                $subtotal += $lineSubtotal;
                $costTotal += $lineCost;
                $lines[] = compact('product', 'quantity', 'unitPrice', 'unitCost', 'lineSubtotal', 'lineCost');
            }

            $discountPercentage = (float) ($payload['discount_percentage'] ?? 0);
            $discountAmount = round($subtotal * ($discountPercentage / 100), 2);
            $afterDiscount = max(0, $subtotal - $discountAmount);
            $serviceRate = (float) $settings->service_charge_rate;
            $serviceAmount = round($afterDiscount * ($serviceRate / 100), 2);
            $taxRate = (float) $settings->tax_rate;
            $taxAmount = round(($afterDiscount + $serviceAmount) * ($taxRate / 100), 2);
            $beforePoints = max(0, $afterDiscount + $serviceAmount + $taxAmount);

            $customer = null;
            $pointsRedeemed = 0;
            $pointsDiscount = 0.0;

            if (! empty($payload['customer_id'])) {
                $customer = Customer::query()->lockForUpdate()->find($payload['customer_id']);
                if (! $customer || ! $customer->is_active) {
                    throw ValidationException::withMessages(['customer_id' => 'Member tidak valid atau sudah tidak aktif.']);
                }
            }

            if ($customer && ! empty($payload['use_points']) && $customer->points >= 20) {
                $pointValue = max(1, (int) $settings->point_value);
                $maxUsefulPoints = (int) floor($beforePoints / $pointValue);
                $pointsRedeemed = min($customer->points, $maxUsefulPoints);
                $pointsDiscount = min($beforePoints, $pointsRedeemed * $pointValue);
            }

            $grandTotal = round(max(0, $beforePoints - $pointsDiscount), 2);
            $paymentMethod = $payload['payment_method'];
            $amountPaid = $paymentMethod === 'Tunai'
                ? round((float) $payload['amount_paid'], 2)
                : $grandTotal;

            if ($amountPaid < $grandTotal) {
                throw ValidationException::withMessages(['amount_paid' => 'Jumlah pembayaran tunai masih kurang.']);
            }

            $change = round(max(0, $amountPaid - $grandTotal), 2);
            $grossProfit = round(max(0, ($subtotal - $discountAmount - $pointsDiscount) - $costTotal), 2);

            $transaction = Transaction::query()->create([
                'outlet_id' => $outlet->id,
                'user_id' => $user->id,
                'customer_id' => $customer?->id,
                'invoice_number' => $this->references->invoice($outlet),
                'transacted_at' => now($outlet->timezone),
                'status' => 'paid',
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'service_charge_percentage' => $serviceRate,
                'service_charge_amount' => $serviceAmount,
                'tax_percentage' => $taxRate,
                'tax_amount' => $taxAmount,
                'points_redeemed' => $pointsRedeemed,
                'points_discount_amount' => $pointsDiscount,
                'grand_total' => $grandTotal,
                'amount_paid' => $amountPaid,
                'change_amount' => $change,
                'cost_total' => $costTotal,
                'gross_profit' => $grossProfit,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $before = $product->stock;
                $after = $before - $line['quantity'];

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'category_name' => $product->category?->name,
                    'unit_cost' => $line['unitCost'],
                    'unit_price' => $line['unitPrice'],
                    'quantity' => $line['quantity'],
                    'line_subtotal' => $line['lineSubtotal'],
                    'line_cost' => $line['lineCost'],
                    'line_profit' => round($line['lineSubtotal'] - $line['lineCost'], 2),
                ]);

                $product->update(['stock' => $after]);

                StockMovement::query()->create([
                    'outlet_id' => $outlet->id,
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->id,
                    'type' => 'sale',
                    'quantity_change' => -$line['quantity'],
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reference' => $transaction->invoice_number,
                    'notes' => 'Penjualan POS',
                ]);
            }

            if ($customer) {
                if ($pointsRedeemed > 0) {
                    $customer->decrement('points', $pointsRedeemed);
                    $customer->refresh();
                    LoyaltyTransaction::query()->create([
                        'customer_id' => $customer->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'redeem',
                        'points_change' => -$pointsRedeemed,
                        'balance_after' => $customer->points,
                        'notes' => "Penukaran poin pada {$transaction->invoice_number}",
                    ]);
                }

                $earned = (int) floor($grandTotal / max(1, (int) $settings->points_per_amount));
                if ($earned > 0) {
                    $customer->increment('points', $earned);
                    $customer->refresh();
                    LoyaltyTransaction::query()->create([
                        'customer_id' => $customer->id,
                        'transaction_id' => $transaction->id,
                        'type' => 'earn',
                        'points_change' => $earned,
                        'balance_after' => $customer->points,
                        'notes' => "Poin dari {$transaction->invoice_number}",
                    ]);
                }

                $customer->update([
                    'last_visit_at' => now($outlet->timezone),
                    'tier' => $this->tierForPoints($customer->points),
                ]);
            }

            return $transaction->load(['items', 'customer', 'user', 'outlet']);
        }, 3);
    }

    private function tierForPoints(int $points): string
    {
        return match (true) {
            $points >= 100 => 'Gold',
            $points >= 50 => 'Silver',
            default => 'Bronze',
        };
    }
}
