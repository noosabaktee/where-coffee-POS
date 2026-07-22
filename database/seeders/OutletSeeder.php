<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = [
            ['code' => 'UTAMA', 'name' => 'Where Coffee - Cabang Utama', 'address' => 'Jl. Premium Boulevard No. 1, Jakarta Selatan', 'phone' => '021-555-0101'],
            ['code' => 'SELATAN', 'name' => 'Where Coffee - Cabang Selatan', 'address' => 'Jl. Cilandak Tengah No. 27, Jakarta Selatan', 'phone' => '021-555-0202'],
            ['code' => 'UTARA', 'name' => 'Where Coffee - Cabang Utara', 'address' => 'Jl. Boulevard Raya Blok QJ 3, Jakarta Utara', 'phone' => '021-555-0303'],
        ];

        foreach ($outlets as $outlet) {
            Outlet::query()->updateOrCreate(['code' => $outlet['code']], [
                ...$outlet,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]);
        }
    }
}
