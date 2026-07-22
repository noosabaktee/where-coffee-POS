<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cost = fake()->numberBetween(7000, 25000);
        return [
            'outlet_id' => Outlet::factory(),
            'category_id' => Category::factory(),
            'sku' => 'PRD-'.Str::upper(Str::random(8)),
            'barcode' => fake()->unique()->ean8(),
            'name' => Str::title(fake()->words(3, true)),
            'cost_price' => $cost,
            'selling_price' => $cost + fake()->numberBetween(8000, 25000),
            'stock' => fake()->numberBetween(10, 100),
            'min_stock' => 5,
            'unit' => 'porsi',
            'is_active' => true,
        ];
    }
}
