<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockEntryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', StockEntry::class);

        $entries = StockEntry::with('product', 'user')->latest()->paginate(15);
        return view('admin.stock-entries.index', compact('entries'));
    }

    public function show(StockEntry $stockEntry): View
    {
        $this->authorize('view', $stockEntry);

        $stockEntry->load('product', 'user');
        return view('admin.stock-entries.show', compact('stockEntry'));
    }

    public function destroy(StockEntry $stockEntry): RedirectResponse
    {
        $this->authorize('delete', $stockEntry);

        $stockEntry->product()->decrement('stock', $stockEntry->quantity);
        $stockEntry->delete();

        return redirect()->route('admin.stock-entries.index')
            ->with('status', 'Entrada de stock eliminada correctamente.');
    }
}
