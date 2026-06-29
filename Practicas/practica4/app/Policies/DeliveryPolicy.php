<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor', 'repartidor']);
    }

    public function view(User $user, Delivery $delivery): bool
    {
        if (in_array($user->role, ['admin', 'supervisor'])) return true;
        if ($user->role === 'repartidor' && $delivery->repartidor_id === $user->id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function updateStatus(User $user, Delivery $delivery): bool
    {
        if ($user->role === 'repartidor' && $delivery->repartidor_id === $user->id) return true;
        return $user->role === 'admin';
    }

    public function delete(User $user, Delivery $delivery): bool
    {
        return $user->role === 'admin';
    }
}
