<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $utama = Outlet::query()->where('code', 'UTAMA')->firstOrFail();
        $selatan = Outlet::query()->where('code', 'SELATAN')->firstOrFail();
        $utara = Outlet::query()->where('code', 'UTARA')->firstOrFail();

        $users = [
            ['name' => 'Administrator Where Coffee', 'username' => 'admin', 'email' => 'admin@wherecoffee.test', 'password' => '123456', 'role' => 'Administrator', 'outlet_id' => null],
            ['name' => 'Andi Pratama', 'username' => 'kasir1', 'email' => 'andi@wherecoffee.test', 'password' => '123456', 'role' => 'Kasir', 'outlet_id' => $utama->id],
            ['name' => 'Siti Rahma', 'username' => 'kasir2', 'email' => 'siti@wherecoffee.test', 'password' => '123456', 'role' => 'Kasir', 'outlet_id' => $selatan->id],
            ['name' => 'Dimas Nugraha', 'username' => 'kasir3', 'email' => 'dimas@wherecoffee.test', 'password' => '123456', 'role' => 'Kasir', 'outlet_id' => $utara->id],
            ['name' => 'Budi Santoso', 'username' => 'managerutama', 'email' => 'budi@wherecoffee.test', 'password' => '123456', 'role' => 'Outlet', 'outlet_id' => $utama->id],
            ['name' => 'Maya Lestari', 'username' => 'manager2', 'email' => 'maya@wherecoffee.test', 'password' => '123456', 'role' => 'Outlet', 'outlet_id' => $selatan->id],
            ['name' => 'Rizky Hidayat', 'username' => 'manager3', 'email' => 'rizky@wherecoffee.test', 'password' => '123456', 'role' => 'Outlet', 'outlet_id' => $utara->id],
        ];

        foreach ($users as $row) {
            $role = $row['role'];
            unset($row['role']);
            $row['password'] = Hash::make($row['password']);
            $row['is_active'] = true;
            $user = User::query()->updateOrCreate(['username' => $row['username']], $row);
            $user->syncRoles([$role]);
        }
    }
}
