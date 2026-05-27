<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movement;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ApiDeliveryController extends Controller implements HasMiddleware
{
    // [JUSTIFICACIÓN] Los permisos API usan guard explícito 'api'.
    // El middleware permission:confirmar entrega,api evalúa contra el guard api.
    public static function middleware(): array
    {
        return [
            new Middleware('permission:confirmar entrega,api', only: ['confirm']),
        ];
    }

    public function confirm(int $id): JsonResponse
    {
        $movement = Movement::where('type', 'exit')
            ->whereNull('confirmed_at')
            ->findOrFail($id);

        $movement->update([
            'confirmed_at' => now(),
            'repartidor_id' => request()->user()->id,
        ]);

        return response()->json([
            'message' => 'Entrega confirmada exitosamente.',
            'movement_id' => $movement->id,
        ]);
    }
}
