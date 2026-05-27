@extends('layouts.app')

@section('title', 'Registrar Movimiento')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Registrar Movimiento</h1>

    <form action="{{ route('movements.store') }}" method="POST" class="bg-white rounded shadow p-6 max-w-lg">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Producto</label>
            <select name="product_id" required
                    class="w-full border rounded px-3 py-2 @error('product_id') border-red-500 @enderror">
                <option value="">Seleccionar producto</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} (SKU: {{ $product->sku }})
                    </option>
                @endforeach
            </select>
            @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Tipo</label>
            <select name="type" required class="w-full border rounded px-3 py-2">
                <option value="entry" {{ old('type') === 'entry' ? 'selected' : '' }}>Entrada</option>
                <option value="exit" {{ old('type') === 'exit' ? 'selected' : '' }}>Salida</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Cantidad</label>
            <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required
                   class="w-full border rounded px-3 py-2 @error('quantity') border-red-500 @enderror">
            @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Almacén</label>
            <select name="warehouse_id" required class="w-full border rounded px-3 py-2">
                <option value="">Seleccionar almacén</option>
                @foreach (\App\Models\Warehouse::all() as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Notas</label>
            <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Registrar
            </button>
            <a href="{{ route('movements.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                Cancelar
            </a>
        </div>
    </form>
@endsection
