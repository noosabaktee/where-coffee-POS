<?php

namespace App\Http\Resources;

use App\Services\MenuPermissionMap;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->getRoleNames()->first(),
            'outlet_id' => $this->outlet_id,
            'outlet' => $this->outlet?->name ?? 'Semua Outlet',
            'is_active' => $this->is_active,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'menus' => MenuPermissionMap::menuIdsForUser($this->resource),
        ];
    }
}
