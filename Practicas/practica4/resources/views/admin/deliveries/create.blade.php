<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Entrega</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.deliveries.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="dispatch_order_id" value="Orden de Despacho" />
                            <select id="dispatch_order_id" name="dispatch_order_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Seleccionar orden...</option>
                                @foreach ($dispatchOrders as $order)
                                    <option value="{{ $order->id }}" @selected(old('dispatch_order_id') == $order->id)>
                                        #{{ $order->id }} — {{ $order->destination ?? 'Sin destino' }} ({{ $order->items->sum('quantity') }} prod.)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('dispatch_order_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="repartidor_id" value="Repartidor" />
                            <select id="repartidor_id" name="repartidor_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">Seleccionar repartidor...</option>
                                @foreach ($repartidores as $repartidor)
                                    <option value="{{ $repartidor->id }}" @selected(old('repartidor_id') == $repartidor->id)>{{ $repartidor->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('repartidor_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="recipient_name" value="Nombre del receptor" />
                            <x-text-input id="recipient_name" class="block mt-1 w-full" type="text" name="recipient_name" :value="old('recipient_name')" required />
                            <x-input-error :messages="$errors->get('recipient_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="address" value="Dirección de entrega" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" required />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" value="Teléfono (opcional)" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="notes" value="Notas (opcional)" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.deliveries.index') }}" class="text-gray-600 hover:text-gray-900">Cancelar</a>
                            <x-primary-button>Asignar Entrega</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
