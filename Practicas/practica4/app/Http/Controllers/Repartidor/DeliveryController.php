<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Delivery::class);

        $deliveries = Delivery::with('dispatchOrder')
            ->where('repartidor_id', request()->user()->id)
            ->latest()
            ->paginate(15);

        return view('repartidor.deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery): View
    {
        $this->authorize('view', $delivery);

        $delivery->load('dispatchOrder.items.product', 'statusLogs.changer');
        return view('repartidor.deliveries.show', compact('delivery'));
    }

    public function updateStatus(Request $request, Delivery $delivery): RedirectResponse
    {
        $this->authorize('updateStatus', $delivery);

        $data = $request->validate([
            'status'         => ['required', 'string', 'in:in_transit,delivered,failed'],
            'failure_reason' => ['required_if:status,failed', 'string', 'max:2000'],
            'notes'          => ['nullable', 'string', 'max:2000'],
        ]);

        $oldStatus = $delivery->status;

        $updateData = [
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? $delivery->notes,
        ];

        if ($data['status'] === 'failed') {
            $updateData['failure_reason'] = $data['failure_reason'];
        }

        $delivery->update($updateData);

        DeliveryStatusLog::create([
            'delivery_id' => $delivery->id,
            'old_status'  => $oldStatus,
            'new_status'  => $data['status'],
            'changed_by'  => $request->user()->id,
            'notes'       => $data['notes'] ?? null,
            'created_at'  => now(),
        ]);

        return redirect()->route('repartidor.deliveries.index')
            ->with('status', 'Estado de entrega actualizado correctamente.');
    }
}
