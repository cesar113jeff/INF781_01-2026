<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Producto: {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                            <dd class="text-lg">{{ $product->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                            <dd class="text-lg">{{ $product->category?->name ?? 'Sin categoría' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                            <dd class="text-lg">{{ $product->description ?? 'Sin descripción' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Precio</dt>
                            <dd class="text-lg">Bs {{ number_format($product->price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Stock</dt>
                            <dd class="text-lg">{{ $product->stock }} unidades</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                            <dd class="text-lg">{{ $product->creator->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Creado el</dt>
                            <dd class="text-lg">{{ $product->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600">
                            Editar
                        </a>
                        <a href="{{ route('admin.products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400">
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
