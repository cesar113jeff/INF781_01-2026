<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Orden de Despacho</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('almacenista.dispatch-orders.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="destination" value="Destino" />
                            <x-text-input id="destination" class="block mt-1 w-full" type="text" name="destination" :value="old('destination')" />
                            <x-input-error :messages="$errors->get('destination')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" value="Notas" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label value="Productos" />
                            <div id="items-wrapper" class="space-y-3">
                                <div class="flex gap-3 items-start item-row">
                                    <select name="items[0][product_id]" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (stock: {{ $product->stock }})</option>
                                        @endforeach
                                    </select>
                                    <x-text-input type="number" name="items[0][quantity]" placeholder="Cantidad" min="1" class="w-32" required />
                                    <button type="button" onclick="this.closest('.item-row').remove()" class="text-red-500 hover:text-red-700 mt-2">X</button>
                                </div>
                            </div>
                            <button type="button" onclick="addItem()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">+ Agregar producto</button>
                            <x-input-error :messages="$errors->get('items')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('almacenista.dispatch-orders.index') }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
                            <x-primary-button>Crear Orden</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    let itemIndex = 1;
    const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'stock' => $p->stock]));
    function addItem() {
        const wrapper = document.getElementById('items-wrapper');
        const options = products.map(p =>
            `<option value="${p.id}">${p.name} (stock: ${p.stock})</option>`
        ).join('');
        const html = `
            <div class="flex gap-3 items-start item-row">
                <select name="items[\${itemIndex}][product_id]" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                    <option value="">Seleccionar...</option>
                    \${options}
                </select>
                <input type="number" name="items[\${itemIndex}][quantity]" placeholder="Cantidad" min="1" required class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" onclick="this.closest('.item-row').remove()" class="text-red-500 hover:text-red-700 mt-2">X</button>
            </div>
        `;
        wrapper.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    }
</script>
