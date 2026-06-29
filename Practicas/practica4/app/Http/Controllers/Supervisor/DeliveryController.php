<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Delivery::class);

        $deliveries = Delivery::with('dispatchOrder', 'repartidor')->latest()->paginate(15);
        return view('supervisor.deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery): View
    {
        $this->authorize('view', $delivery);

        $delivery->load('dispatchOrder.items.product', 'repartidor', 'statusLogs.changer');
        return view('supervisor.deliveries.show', compact('delivery'));
    }
}
