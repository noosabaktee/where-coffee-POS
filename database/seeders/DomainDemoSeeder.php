<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DomainDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            ExpenseSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
