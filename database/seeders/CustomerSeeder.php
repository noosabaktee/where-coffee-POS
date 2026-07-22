<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Hendra Wijaya', 'Nadia Putri', 'Kevin Jonathan', 'Ayu Maharani', 'Fajar Ramadhan',
            'Citra Dewi', 'Raka Aditya', 'Melisa Tan', 'Bagus Saputra', 'Intan Permata',
            'Samuel Hartono', 'Vina Amelia', 'Arif Kurniawan', 'Jessica Lim', 'Taufik Akbar',
            'Dinda Larasati', 'Yusuf Maulana', 'Clara Natalia', 'Reza Pahlevi', 'Monica Angelica',
        ];

        foreach ($names as $index => $name) {
            $points = [125, 84, 22, 48, 15, 66, 31, 105, 9, 54, 72, 18, 43, 98, 26, 58, 12, 112, 37, 76][$index];
            $tier = $points >= 100 ? 'Gold' : ($points >= 50 ? 'Silver' : 'Bronze');
            Customer::query()->updateOrCreate(['phone' => '0812'.str_pad((string) (34560000 + $index), 8, '0', STR_PAD_LEFT)], [
                'member_code' => 'MBR-'.str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'name' => $name,
                'email' => 'member'.($index + 1).'@example.test',
                'tier' => $tier,
                'points' => $points,
                'birth_date' => now()->subYears(20 + ($index % 16))->subDays($index * 9)->toDateString(),
                'last_visit_at' => now()->subDays($index % 12)->subHours($index % 8),
                'is_active' => true,
            ]);
        }
    }
}
