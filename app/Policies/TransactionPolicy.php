<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reports.view');
    }
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->can('reports.view') && $user->canAccessOutlet($transaction->outlet_id);
    }
    public function create(User $user): bool
    {
        return $user->can('pos.use');
    }
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->can('reports.delete') && $user->canAccessOutlet($transaction->outlet_id);
    }
}
