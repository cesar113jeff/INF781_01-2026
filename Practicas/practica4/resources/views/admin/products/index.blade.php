<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Productos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Nuevo Producto
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Nombre</th>
                                <th class="text-left py-2">Categoría</th>
                                <th class="text-left py-2">Precio</th>
                                <th class="text-left py-2">Stock</th>
                                <th class="text-left py-2">Creado por</th>
                                <th class="text-right py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr class="border-b">
                                    <td class="py-2">{{ $product->name }}</td>
                                    <td class="py-2">{{ $product->category?->name ?? '—' }}</td>
                                    <td class="py-2">Bs {{ number_format($product->price, 2) }}</td>
                                    <td class="py-2">{{ $product->stock }}</td>
                                    <td class="py-2">{{ $product->creator->name }}</td>
                                    <td class="text-right py-2">
                                        <a href="{{ route('admin.products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Ver</a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Editar</a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Eliminar producto?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
