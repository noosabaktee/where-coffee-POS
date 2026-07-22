<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['Bahan Baku', 'Pembelian biji kopi house blend 15 kg', 2850000],
            ['Bahan Baku', 'Susu UHT dan alternatif oat milk', 1450000],
            ['Utilitas', 'Tagihan listrik dan air outlet', 2300000],
            ['Gaji Karyawan', 'Payroll staff operasional', 12500000],
            ['Promosi & Marketing', 'Iklan media sosial dan voucher', 1350000],
            ['Perawatan', 'Servis mesin espresso dan grinder', 975000],
            ['Lain-lain', 'Perlengkapan kebersihan dan kemasan', 680000],
        ];

        $expenseCount = app()->environment('testing') ? 20 : 119;

        foreach (Outlet::query()->get() as $outletIndex => $outlet) {
            $creator = User::query()->where('outlet_id', $outlet->id)->role('Outlet')->first()
                ?? User::query()->role('Administrator')->first();

            foreach (range(0, $expenseCount) as $i) {
                $template = $templates[($i + $outletIndex) % count($templates)];
                $date = now($outlet->timezone)->subDays(($i * 2) + $outletIndex);
                Expense::query()->updateOrCreate([
                    'expense_number' => sprintf('EXP-%s-%s-%02d', $outlet->code, $date->format('ymd'), $i + 1),
                ], [
                    'outlet_id' => $outlet->id,
                    'created_by' => $creator?->id,
                    'expense_date' => $date->toDateString(),
                    'category' => $template[0],
                    'description' => $template[1],
                    'amount' => round($template[2] * (1 + ($outletIndex * 0.04)) * (0.9 + (($i % 5) * 0.05)), -2),
                    'payment_method' => $i % 3 === 0 ? 'Transfer' : 'Tunai',
                    'notes' => 'Data realistis untuk demonstrasi laporan operasional.',
                ]);
            }
        }
    }
}
