<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ProductController extends Controller implements HasMiddleware
{
    // [JUSTIFICACIÓN] Se usa HasMiddleware con método estático middleware()
    // en lugar de $this->middleware() en el constructor, que está deprecado en Laravel 13.
    // Los atributos se definen a nivel de método con only/except.
    public static function middleware(): array
    {
        return [
            new Middleware('permission:ver productos', only: ['index', 'show']),
            new Middleware('permission:crear productos', only: ['create', 'store']),
            new Middleware('permission:editar productos', only: ['edit', 'update']),
            new Middleware('permission:eliminar productos', only: ['destroy']),
            // [JUSTIFICACIÓN] role_or_permission demuestra middleware compuesto:
            // un admin (por rol) o cualquier usuario con 'crear productos' puede acceder.
            new Middleware('role_or_permission:admin|crear productos', only: ['create']),
        ];
    }

    public function index(): View
    {
        $products = Product::paginate(15);
        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
}
