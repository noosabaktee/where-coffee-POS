<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\OutletSetting;
use Illuminate\Database\Seeder;

class OutletSettingSeeder extends Seeder
{
    public function run(): void
    {
        Outlet::query()->each(function (Outlet $outlet): void {
            OutletSetting::query()->updateOrCreate(['outlet_id' => $outlet->id], [
                'store_name' => $outlet->name,
                'address' => $outlet->address,
                'phone' => $outlet->phone,
                'tax_rate' => 10,
                'service_charge_rate' => $outlet->code === 'UTAMA' ? 5 : 0,
                'currency' => 'IDR',
                'timezone' => $outlet->timezone,
                'receipt_footer' => 'Terima kasih sudah ngopi bersama Where Coffee ☕',
                'points_per_amount' => 10000,
                'point_value' => 500,
            ]);
        });
    }
}
