<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Movimiento de Inventario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('almacenista.movements.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="product_id" value="Producto" />
                            <select id="product_id" name="product_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Seleccionar producto</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} (Stock: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('product_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="type" value="Tipo de movimiento" />
                            <select id="type" name="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="in" {{ old('type') === 'in' ? 'selected' : '' }}>Entrada (ingreso de stock)</option>
                                <option value="out" {{ old('type') === 'out' ? 'selected' : '' }}>Salida (venta/egreso)</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="quantity" value="Cantidad" />
                            <x-text-input id="quantity" class="block mt-1 w-full" name="quantity" type="number" min="1" :value="old('quantity')" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="notes" value="Notas (opcional)" />
                            <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('almacenista.movements.index') }}" class="text-sm text-gray-600 mr-4">Cancelar</a>
                            <x-primary-button>Registrar Movimiento</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
