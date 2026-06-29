<?php

namespace App\Policies;

use App\Models\StockEntry;
use App\Models\User;

class StockEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenista', 'supervisor']);
    }

    public function view(User $user, StockEntry $stockEntry): bool
    {
        return in_array($user->role, ['admin', 'almacenista', 'supervisor']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenista']);
    }

    public function delete(User $user, StockEntry $stockEntry): bool
    {
        return $user->role === 'admin';
    }
}
