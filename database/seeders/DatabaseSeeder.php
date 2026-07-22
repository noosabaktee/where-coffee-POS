<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OutletSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            OutletSettingSeeder::class,
            DomainDemoSeeder::class,
        ]);
    }
}
