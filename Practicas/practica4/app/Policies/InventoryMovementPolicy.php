<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;

class InventoryMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenista', 'supervisor']);
    }

    public function view(User $user, InventoryMovement $movement): bool
    {
        return in_array($user->role, ['admin', 'almacenista', 'supervisor']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenista']);
    }
}
