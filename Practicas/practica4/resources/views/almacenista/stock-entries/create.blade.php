<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Entrada de Stock</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('almacenista.stock-entries.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="product_id" value="Producto" />
                            <select id="product_id" name="product_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Seleccionar producto...</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                        {{ $product->name }} (stock: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="supplier" value="Proveedor" />
                            <x-text-input id="supplier" class="block mt-1 w-full" type="text" name="supplier" :value="old('supplier')" required />
                            <x-input-error :messages="$errors->get('supplier')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="quantity" value="Cantidad" />
                            <x-text-input id="quantity" class="block mt-1 w-full" type="number" name="quantity" :value="old('quantity')" min="1" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" value="Notas (opcional)" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('almacenista.stock-entries.index') }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
                            <x-primary-button>Registrar Entrada</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
