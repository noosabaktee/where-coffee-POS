<?php

namespace App\Services;

class MenuPermissionMap
{
    /** @return array<string, array{label:string, permissions:list<string>}> */
    public static function all(): array
    {
        return [
            'dashboard' => ['label' => 'Dashboard', 'permissions' => ['menu.dashboard', 'dashboard.view']],
            'analytic' => ['label' => 'Analisis Bisnis', 'permissions' => ['menu.analytic', 'analytics.view']],
            'pos' => ['label' => 'Sistem Kasir (POS)', 'permissions' => ['menu.pos', 'pos.use', 'products.view', 'categories.view', 'customers.view']],
            'inventori' => ['label' => 'Manajemen Stok', 'permissions' => ['menu.inventori', 'inventory.view', 'products.view', 'products.create', 'products.update', 'products.delete']],
            'laporan' => ['label' => 'Keuangan & Laporan', 'permissions' => ['menu.laporan', 'reports.view', 'reports.export']],
            'biaya' => ['label' => 'Biaya Operasional', 'permissions' => ['menu.biaya', 'expenses.view', 'expenses.create', 'expenses.update', 'expenses.delete']],
            'kategori' => ['label' => 'Master Kategori', 'permissions' => ['menu.kategori', 'categories.view', 'categories.create', 'categories.update', 'categories.delete']],
            'setting' => ['label' => 'Pengaturan Toko', 'permissions' => ['menu.setting', 'settings.view', 'settings.update', 'users.view', 'users.create', 'users.update', 'users.delete']],
            'outlets' => ['label' => 'Kelola Cabang', 'permissions' => ['menu.outlet', 'outlets.view', 'outlets.create', 'outlets.update', 'outlets.delete', 'outlets.switch']],
            'crm' => ['label' => 'Manajemen CRM', 'permissions' => ['menu.crm', 'customers.view', 'customers.create', 'customers.update', 'customers.delete']],
        ];
    }

    /** @return array<string, string> */
    public static function routeNames(): array
    {
        return [
            'dashboard' => 'dashboard',
            'analytic' => 'analytics',
            'pos' => 'pos',
            'inventori' => 'inventory',
            'laporan' => 'reports',
            'biaya' => 'expenses',
            'kategori' => 'categories',
            'setting' => 'settings',
            'outlets' => 'outlets',
            'crm' => 'crm',
        ];
    }

    public static function defaultRouteNameForUser(object $user): string
    {
        $firstMenu = self::menuIdsForUser($user)[0] ?? 'dashboard';

        return self::routeNames()[$firstMenu] ?? 'dashboard';
    }

    /** @return list<string> */
    public static function menuIdsForUser(object $user): array
    {
        if ($user->hasRole('Administrator')) {
            return array_keys(self::all());
        }

        return collect(self::all())
            ->filter(fn (array $menu) => $user->can($menu['permissions'][0]))
            ->keys()
            ->values()
            ->all();
    }

    /** @param list<string> $menuIds @return list<string> */
    public static function permissionsForMenus(array $menuIds): array
    {
        return collect($menuIds)
            ->filter(fn (string $id) => array_key_exists($id, self::all()))
            ->flatMap(fn (string $id) => self::all()[$id]['permissions'])
            ->unique()
            ->values()
            ->all();
    }
}
