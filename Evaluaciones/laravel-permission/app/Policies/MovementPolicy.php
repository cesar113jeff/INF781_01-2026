<?php

namespace App\Policies;

use App\Models\Movement;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MovementPolicy
{
    /**
     * [JUSTIFICACIÓN] La acción 'aprobar' solo la puede ejecutar un usuario
     * que posea explícitamente el permiso 'aprobar movimiento'.
     * No se usan comparaciones de strings contra roles.
     */
    public function approve(User $user, Movement $movement): Response
    {
        return $user->hasPermissionTo('aprobar movimiento', 'web')
            ? Response::allow()
            : Response::deny('No tienes permiso para aprobar movimientos.');
    }

    /**
     * [JUSTIFICACIÓN] Si el usuario es almacenista, solo puede registrar
     * movimientos si el warehouse_id del movimiento coincide con su almacén
     * asignado (principio de mínimo privilegio).
     * Si no es almacenista, se verifica que tenga el permiso genérico.
     * Se pasa el movimiento ya creado para validar el warehouse_id.
     */
    public function create(User $user, Movement $movement): Response
    {
        if ($user->hasRole('almacenista')) {
            return $user->warehouse_id === $movement->warehouse_id
                ? Response::allow()
                : Response::deny('Solo puedes registrar movimientos en tu almacén asignado.');
        }

        return $user->hasPermissionTo('registrar movimiento', 'web')
            ? Response::allow()
            : Response::deny('No tienes permiso para registrar movimientos.');
    }
}
