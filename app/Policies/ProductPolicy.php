<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool { return $user->can('products.view'); }
    public function view(User $user, Product $product): bool { return $user->can('products.view') && $user->canAccessOutlet($product->outlet_id); }
    public function create(User $user): bool { return $user->can('products.create'); }
    public function update(User $user, Product $product): bool { return $user->can('products.update') && $user->canAccessOutlet($product->outlet_id); }
    public function delete(User $user, Product $product): bool { return $user->can('products.delete') && $user->canAccessOutlet($product->outlet_id); }
}
