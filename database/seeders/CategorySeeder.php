<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'CAT-COF', 'name' => 'Coffee', 'icon' => 'bx-coffee', 'sort_order' => 1],
            ['code' => 'CAT-NCF', 'name' => 'Non-Coffee', 'icon' => 'bx-drink', 'sort_order' => 2],
            ['code' => 'CAT-TEA', 'name' => 'Tea', 'icon' => 'bx-leaf', 'sort_order' => 3],
            ['code' => 'CAT-PST', 'name' => 'Pastry', 'icon' => 'bx-baguette', 'sort_order' => 4],
            ['code' => 'CAT-FOD', 'name' => 'Main Course', 'icon' => 'bx-bowl-hot', 'sort_order' => 5],
            ['code' => 'CAT-SNK', 'name' => 'Snacks', 'icon' => 'bx-cookie', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['code' => $category['code']], [...$category, 'is_active' => true]);
        }
    }
}
