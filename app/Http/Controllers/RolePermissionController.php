<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Services\MenuPermissionMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    public function update(UpdateRolePermissionsRequest $request, string $role): JsonResponse
    {
        abort_if($role === 'Administrator', 422, 'Hak akses Administrator tidak dapat dibatasi.');
        abort_unless(in_array($role, ['Kasir', 'Outlet'], true), 404);

        $roleModel = Role::findByName($role, 'web');
        $roleModel->syncPermissions(MenuPermissionMap::permissionsForMenus($request->validated('menus')));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json([
            'message' => "Hak akses peran {$role} berhasil diperbarui.",
            'menus' => $request->validated('menus'),
        ]);
    }
}
