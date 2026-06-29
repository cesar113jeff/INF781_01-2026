<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DispatchOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Delivery::class);

        $deliveries = Delivery::with('dispatchOrder', 'repartidor')->latest()->paginate(15);
        return view('admin.deliveries.index', compact('deliveries'));
    }

    public function create(): View
    {
        $this->authorize('create', Delivery::class);

        $dispatchOrders = DispatchOrder::where('status', 'approved')
            ->whereDoesntHave('deliveries')
            ->with('items.product')
            ->get();
        $repartidores = User::where('role', 'repartidor')->orderBy('name')->get();
        return view('admin.deliveries.create', compact('dispatchOrders', 'repartidores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Delivery::class);

        $data = $request->validate([
            'dispatch_order_id' => ['required', 'exists:dispatch_orders,id'],
            'repartidor_id'     => ['required', 'exists:users,id'],
            'recipient_name'    => ['required', 'string', 'max:180'],
            'address'           => ['required', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ]);

        $data['status'] = 'assigned';

        Delivery::create($data);

        return redirect()->route('admin.deliveries.index')
            ->with('status', 'Entrega asignada correctamente.');
    }

    public function show(Delivery $delivery): View
    {
        $this->authorize('view', $delivery);

        $delivery->load('dispatchOrder.items.product', 'repartidor', 'statusLogs.changer');
        return view('admin.deliveries.show', compact('delivery'));
    }

    public function destroy(Delivery $delivery): RedirectResponse
    {
        $this->authorize('delete', $delivery);

        $delivery->delete();

        return redirect()->route('admin.deliveries.index')
            ->with('status', 'Entrega eliminada correctamente.');
    }
}
