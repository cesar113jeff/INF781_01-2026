<?php

namespace App\Http\Controllers\Almacenista;

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
        return view('almacenista.stock-entries.index', compact('entries'));
    }

    public function create(): View
    {
        $this->authorize('create', StockEntry::class);

        $products = Product::orderBy('name')->get();
        return view('almacenista.stock-entries.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockEntry::class);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'supplier'   => ['required', 'string', 'max:180'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ]);

        $data['user_id'] = $request->user()->id;

        $entry = StockEntry::create($data);

        $entry->product()->increment('stock', $data['quantity']);

        return redirect()->route('almacenista.stock-entries.index')
            ->with('status', 'Entrada de stock registrada correctamente.');
    }

    public function show(StockEntry $stockEntry): View
    {
        $this->authorize('view', $stockEntry);

        $stockEntry->load('product', 'user');
        return view('almacenista.stock-entries.show', compact('stockEntry'));
    }
}
