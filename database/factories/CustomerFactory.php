<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_code' => 'MBR-'.Str::upper(Str::random(8)),
            'name' => fake()->name(),
            'phone' => fake()->unique()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'tier' => 'Bronze',
            'points' => 0,
            'is_active' => true,
        ];
    }
}
