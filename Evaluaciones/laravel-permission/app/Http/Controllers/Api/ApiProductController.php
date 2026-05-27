<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ApiProductController extends Controller implements HasMiddleware
{
    // [JUSTIFICACIÓN] Los permisos API usan guard explícito 'api'.
    // El middleware permission:ver productos,api evalúa contra el guard api,
    // no contra el guard web por defecto.
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver productos,api', only: ['index']),
        ];
    }

    public function index(): JsonResponse
    {
        $products = Product::all();
        return response()->json($products);
    }
}
