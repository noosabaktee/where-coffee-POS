<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Outlet;
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
}
