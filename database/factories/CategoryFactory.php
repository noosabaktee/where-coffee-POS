<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['type' => Category::TYPE_PRODUCT, 'code' => 'CAT-'.Str::upper(Str::random(6)), 'name' => Str::title($name), 'is_active' => true, 'sort_order' => 0];
    }
}
