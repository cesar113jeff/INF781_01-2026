<?php

namespace App\Policies;

use App\Models\DispatchOrder;
use App\Models\User;

class DispatchOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenista', 'supervisor']);
    }

    public function view(User $user, DispatchOrder $dispatchOrder): bool
    {
        if ($user->role === 'admin') return true;
        if ($user->role === 'supervisor') return true;
        if ($user->role === 'almacenista' && $dispatchOrder->user_id === $user->id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'almacenista']);
    }

    public function delete(User $user, DispatchOrder $dispatchOrder): bool
    {
        return $user->role === 'admin';
    }

    public function approve(User $user, DispatchOrder $dispatchOrder): bool
    {
        return $user->role === 'supervisor' && $dispatchOrder->status === 'pending';
    }
}
