<?php

namespace App\Http\Controllers\Almacenista;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $movements = InventoryMovement::with('product', 'user')
            ->latest()
            ->paginate(15);

        return view('almacenista.movements.index', compact('movements'));
    }

    public function create(): View
    {
        $this->authorize('create', InventoryMovement::class);

        $products = Product::orderBy('name')->get();
        return view('almacenista.movements.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InventoryMovement::class);        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:in,out'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['type'] === 'out' && $product->stock < $data['quantity']) {
            return back()->withErrors([
                'quantity' => 'Stock insuficiente. Disponible: ' . $product->stock,
            ])->withInput();
        }

        $data['user_id'] = $request->user()->id;

        InventoryMovement::create($data);

        if ($data['type'] === 'in') {
            $product->increment('stock', $data['quantity']);
        } else {
            $product->decrement('stock', $data['quantity']);
        }

        return redirect()->route('almacenista.movements.index')
            ->with('status', 'Movimiento registrado correctamente.');
    }

    public function show(InventoryMovement $movement): View
    {
        $this->authorize('view', $movement);

        $movement->load('product', 'user');
        return view('almacenista.movements.show', compact('movement'));
    }
}
