<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class MovementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver productos', only: ['index']),
            new Middleware('permission:registrar movimiento', only: ['create', 'store']),
        ];
    }

    public function index(): View
    {
        $movements = Movement::with(['product', 'warehouse', 'user'])
            ->latest()
            ->paginate(15);
        return view('movements.index', compact('movements'));
    }

    public function create(): View
    {
        $products = Product::all();
        return view('movements.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entry,exit',
            'quantity' => 'required|integer|min:1',
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes' => 'nullable|string',
            'repartidor_id' => 'nullable|exists:users,id',
        ]);

        $movement = Movement::create([
            'product_id' => $validated['product_id'],
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'warehouse_id' => $validated['warehouse_id'],
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'repartidor_id' => $validated['repartidor_id'] ?? null,
        ]);

        // [JUSTIFICACIÓN] La autorización se delega a MovementPolicy mediante
        // $this->authorize(). Se pasa el movimiento ya creado para que la policy
        // pueda validar el warehouse_id contra el usuario (caso almacenista).
        // Si falla, Laravel lanza AuthorizationException y aborta 403.
        $this->authorize('create', $movement);

        return redirect()->route('movements.index')
            ->with('success', 'Movimiento registrado exitosamente.');
    }

    public function approve(Movement $movement): RedirectResponse
    {
        // [JUSTIFICACIÓN] La autorización se delega a MovementPolicy mediante
        // la función can() de Laravel (Gate). No se reimplementa lógica a mano.
        $this->authorize('approve', $movement);

        $movement->update(['status' => 'approved']);

        // Actualizar stock del producto
        $product = $movement->product;
        if ($movement->type === 'entry') {
            $product->increment('stock', $movement->quantity);
        } else {
            $product->decrement('stock', $movement->quantity);
        }

        return redirect()->route('movements.index')
            ->with('success', 'Movimiento aprobado exitosamente.');
    }
}
