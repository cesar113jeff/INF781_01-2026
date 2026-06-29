<?php

namespace App\Http\Controllers\Almacenista;

use App\Http\Controllers\Controller;
use App\Models\DispatchOrder;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispatchOrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DispatchOrder::class);

        $orders = DispatchOrder::with('user', 'items.product')
            ->where('user_id', request()->user()->id)
            ->latest()
            ->paginate(15);

        return view('almacenista.dispatch-orders.index', compact('orders'));
    }

    public function create(): View
    {
        $this->authorize('create', DispatchOrder::class);

        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        return view('almacenista.dispatch-orders.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DispatchOrder::class);

        $data = $request->validate([
            'destination' => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string', 'max:2000'],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $order = DispatchOrder::create([
            'destination' => $data['destination'],
            'notes'       => $data['notes'],
            'status'      => 'pending',
            'user_id'     => $request->user()->id,
        ]);

        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return back()->withErrors([
                    'items' => "Stock insuficiente para {$product->name}. Disponible: {$product->stock}",
                ])->withInput();
            }
            $order->items()->create($item);
            $product->decrement('stock', $item['quantity']);
        }

        return redirect()->route('almacenista.dispatch-orders.index')
            ->with('status', 'Orden de despacho creada correctamente.');
    }

    public function show(DispatchOrder $dispatchOrder): View
    {
        $this->authorize('view', $dispatchOrder);

        $dispatchOrder->load('items.product', 'user', 'approver');
        return view('almacenista.dispatch-orders.show', compact('dispatchOrder'));
    }
}
