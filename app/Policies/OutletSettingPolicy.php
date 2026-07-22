<?php

namespace App\Policies;

use App\Models\OutletSetting;
use App\Models\User;

class OutletSettingPolicy
{
    public function view(User $user, OutletSetting $setting): bool { return $user->can('settings.view') && $user->canAccessOutlet($setting->outlet_id); }
    public function update(User $user, OutletSetting $setting): bool { return $user->can('settings.update') && $user->canAccessOutlet($setting->outlet_id); }
}
