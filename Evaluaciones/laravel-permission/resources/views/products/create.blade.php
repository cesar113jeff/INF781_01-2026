@extends('layouts.app')

@section('title', 'Crear Producto')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Crear Producto</h1>

    <form action="{{ route('products.store') }}" method="POST" class="bg-white rounded shadow p-6 max-w-lg">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">SKU</label>
            <input type="text" name="sku" value="{{ old('sku') }}" required
                   class="w-full border rounded px-3 py-2 @error('sku') border-red-500 @enderror">
            @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea name="description" rows="3"
                      class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Precio</label>
            <input type="number" step="0.01" name="price" value="{{ old('price', 0) }}" required
                   class="w-full border rounded px-3 py-2 @error('price') border-red-500 @enderror">
            @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Stock</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" required
                   class="w-full border rounded px-3 py-2 @error('stock') border-red-500 @enderror">
            @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Guardar
            </button>
            <a href="{{ route('products.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                Cancelar
            </a>
        </div>
    </form>
@endsection
