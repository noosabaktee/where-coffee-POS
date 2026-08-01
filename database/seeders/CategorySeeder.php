<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-COF', 'name' => 'Coffee', 'icon' => 'bx-coffee', 'sort_order' => 1],
            ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-NCF', 'name' => 'Non-Coffee', 'icon' => 'bx-drink', 'sort_order' => 2],
            ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-TEA', 'name' => 'Tea', 'icon' => 'bx-leaf', 'sort_order' => 3],
            ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-PST', 'name' => 'Pastry', 'icon' => 'bx-baguette', 'sort_order' => 4],
            ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-FOD', 'name' => 'Main Course', 'icon' => 'bx-bowl-hot', 'sort_order' => 5],
            ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-SNK', 'name' => 'Snacks', 'icon' => 'bx-cookie', 'sort_order' => 6],
            ['type' => Category::TYPE_EXPENSE, 'code' => 'EXP-BHN', 'name' => 'Bahan Baku', 'icon' => 'bx-receipt', 'sort_order' => 1],
            ['type' => Category::TYPE_EXPENSE, 'code' => 'EXP-UTL', 'name' => 'Utilitas', 'icon' => 'bx-receipt', 'sort_order' => 2],
            ['type' => Category::TYPE_EXPENSE, 'code' => 'EXP-GAJ', 'name' => 'Gaji Karyawan', 'icon' => 'bx-receipt', 'sort_order' => 3],
            ['type' => Category::TYPE_EXPENSE, 'code' => 'EXP-MKT', 'name' => 'Promosi & Marketing', 'icon' => 'bx-receipt', 'sort_order' => 4],
            ['type' => Category::TYPE_EXPENSE, 'code' => 'EXP-PRW', 'name' => 'Perawatan', 'icon' => 'bx-receipt', 'sort_order' => 5],
            ['type' => Category::TYPE_EXPENSE, 'code' => 'EXP-LLN', 'name' => 'Lain-lain', 'icon' => 'bx-receipt', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['type' => $category['type'], 'code' => $category['code']],
                [...$category, 'is_active' => true],
            );
        }
    }
}
