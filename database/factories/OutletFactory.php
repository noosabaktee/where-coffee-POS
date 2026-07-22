<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OutletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('OUT-###??')),
            'name' => 'Where Coffee '.fake()->city(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ];
    }
}
