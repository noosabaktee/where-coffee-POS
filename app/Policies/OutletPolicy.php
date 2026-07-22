<?php

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;

class OutletPolicy
{
    public function viewAny(User $user): bool { return $user->can('outlets.view'); }
    public function view(User $user, Outlet $outlet): bool { return $user->can('outlets.view'); }
    public function create(User $user): bool { return $user->can('outlets.create'); }
    public function update(User $user, Outlet $outlet): bool { return $user->can('outlets.update'); }
    public function delete(User $user, Outlet $outlet): bool { return $user->can('outlets.delete'); }
}
