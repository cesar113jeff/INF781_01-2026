<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryStatusLog;
use App\Models\DispatchOrder;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        $totalProducts = Product::count();
        $totalUsers = User::count();
        $usersByRole = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');
        $lowStockProducts = Product::where('stock', '<', 10)->orderBy('stock')->get();
        $totalStockEntries = StockEntry::count();
        $dispatchCounts = DispatchOrder::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $deliveryCounts = Delivery::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $recentMovements = InventoryMovement::with('product', 'user')->latest()->take(10)->get();
        $recentLogs = DeliveryStatusLog::with('delivery', 'changer')->latest('created_at')->take(10)->get();

        return view('admin.reports.index', compact(
            'totalProducts', 'totalUsers', 'usersByRole',
            'lowStockProducts', 'totalStockEntries',
            'dispatchCounts', 'deliveryCounts',
            'recentMovements', 'recentLogs',
        ));
    }
}
