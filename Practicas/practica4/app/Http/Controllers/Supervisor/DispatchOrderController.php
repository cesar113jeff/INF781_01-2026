<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\DispatchOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispatchOrderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DispatchOrder::class);

        $orders = DispatchOrder::with('user', 'items.product')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'approved' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(15);

        return view('supervisor.dispatch-orders.index', compact('orders'));
    }

    public function show(DispatchOrder $dispatchOrder): View
    {
        $this->authorize('view', $dispatchOrder);

        $dispatchOrder->load('items.product', 'user', 'approver');
        return view('supervisor.dispatch-orders.show', compact('dispatchOrder'));
    }

    public function approve(DispatchOrder $dispatchOrder): RedirectResponse
    {
        $this->authorize('approve', $dispatchOrder);

        $dispatchOrder->update([
            'status'      => 'approved',
            'approved_by' => request()->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('supervisor.dispatch-orders.index')
            ->with('status', 'Orden de despacho aprobada correctamente.');
    }

    public function reject(Request $request, DispatchOrder $dispatchOrder): RedirectResponse
    {
        $this->authorize('approve', $dispatchOrder);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $dispatchOrder->update([
            'status'           => 'rejected',
            'approved_by'      => $request->user()->id,
            'approved_at'      => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);

        foreach ($dispatchOrder->items as $item) {
            $item->product()->increment('stock', $item->quantity);
        }

        return redirect()->route('supervisor.dispatch-orders.index')
            ->with('status', 'Orden de despacho rechazada.');
    }
}
