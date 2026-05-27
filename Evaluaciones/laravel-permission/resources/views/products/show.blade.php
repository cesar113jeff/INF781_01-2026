@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
        <div>
            @can('editar productos')
                <a href="{{ route('products.edit', $product) }}"
                   class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                    Editar
                </a>
            @endcan
            @can('eliminar productos')
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                      onsubmit="return confirm('¿Eliminar este producto?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Eliminar
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">SKU</dt>
                <dd class="font-medium">{{ $product->sku }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Precio</dt>
                <dd class="font-medium">${{ number_format($product->price, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Stock</dt>
                <dd class="font-medium">{{ $product->stock }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Creado</dt>
                <dd class="font-medium">{{ $product->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        @if ($product->description)
            <div class="mt-4">
                <dt class="text-sm text-gray-500">Descripción</dt>
                <dd class="mt-1">{{ $product->description }}</dd>
            </div>
        @endif
    </div>

    <a href="{{ route('products.index') }}" class="inline-block mt-4 text-blue-600 hover:underline">
        &larr; Volver a productos
    </a>
@endsection
