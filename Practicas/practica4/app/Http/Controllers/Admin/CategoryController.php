<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::withCount('products')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('status', 'Categoría creada correctamente.');
    }

    public function show(Category $category): View
    {
        $this->authorize('view', $category);

        $category->loadCount('products');
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:120', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('status', 'Categoría eliminada correctamente.');
    }
}
