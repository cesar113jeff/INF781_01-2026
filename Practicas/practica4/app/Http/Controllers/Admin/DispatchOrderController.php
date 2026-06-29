<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DispatchOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DispatchOrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DispatchOrder::class);

        $orders = DispatchOrder::with('user', 'items.product')
            ->latest()
            ->paginate(15);

        return view('admin.dispatch-orders.index', compact('orders'));
    }

    public function show(DispatchOrder $dispatchOrder): View
    {
        $this->authorize('view', $dispatchOrder);

        $dispatchOrder->load('items.product', 'user', 'approver');
        return view('admin.dispatch-orders.show', compact('dispatchOrder'));
    }

    public function destroy(DispatchOrder $dispatchOrder): RedirectResponse
    {
        $this->authorize('delete', $dispatchOrder);

        foreach ($dispatchOrder->items as $item) {
            $item->product()->increment('stock', $item->quantity);
        }
        $dispatchOrder->delete();

        return redirect()->route('admin.dispatch-orders.index')
            ->with('status', 'Orden de despacho eliminada correctamente.');
    }
}
