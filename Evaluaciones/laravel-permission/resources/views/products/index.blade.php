@extends('layouts.app')

@section('title', 'Productos')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Productos</h1>
        @can('crear productos')
            <a href="{{ route('products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Crear Producto
            </a>
        @endcan
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">SKU</th>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-right">Precio</th>
                    <th class="px-4 py-2 text-right">Stock</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $product->sku }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:underline">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-right">${{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ $product->stock }}</td>
                        <td class="px-4 py-2 text-center">
                            @can('editar productos')
                                <a href="{{ route('products.edit', $product) }}" class="text-yellow-600 hover:underline mr-2">
                                    Editar
                                </a>
                            @endcan

                            @can('eliminar productos')
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿Eliminar este producto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                            No hay productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
