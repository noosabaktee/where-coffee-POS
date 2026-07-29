<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Str;

class ReferenceNumberService
{
    public function invoice(Outlet $outlet): string
    {
        do {
            $number = sprintf(
                'WC-%s-%s-%s',
                Str::upper(Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $outlet->code), 5, '')),
                now($outlet->timezone)->format('ymd'),
                Str::upper(Str::random(6)),
            );
        } while (Transaction::query()->where('invoice_number', $number)->exists());

        return $number;
    }

    public function expense(Outlet $outlet): string
    {
        do {
            $number = sprintf(
                'EXP-%s-%s-%s',
                Str::upper(Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $outlet->code), 5, '')),
                now($outlet->timezone)->format('ymd'),
                Str::upper(Str::random(4)),
            );
        } while (Expense::query()->where('expense_number', $number)->exists());

        return $number;
    }

    public function productSku(Outlet $outlet): string
    {
        $prefix = Str::upper((string) preg_replace('/[^A-Za-z0-9]/', '', $outlet->code)).'-';
        $lastSequence = Product::query()
            ->forOutlet($outlet)
            ->where('sku', 'like', $prefix.'%')
            ->pluck('sku')
            ->map(function (string $sku) use ($prefix): int {
                return preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $sku, $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->max() ?? 0;

        do {
            $lastSequence++;
            $sku = $prefix.str_pad((string) $lastSequence, 3, '0', STR_PAD_LEFT);
        } while (Product::query()->forOutlet($outlet)->where('sku', $sku)->exists());

        return $sku;
    }
}
