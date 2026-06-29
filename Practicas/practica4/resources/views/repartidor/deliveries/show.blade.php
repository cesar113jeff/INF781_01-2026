<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Entrega: {{ $delivery->recipient_name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <dl class="divide-y divide-gray-200">
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Receptor</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $delivery->recipient_name }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $delivery->address }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $delivery->phone ?? '—' }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Estado</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($delivery->status === 'assigned') bg-blue-100 text-blue-700
                                    @elseif($delivery->status === 'in_transit') bg-yellow-100 text-yellow-700
                                    @elseif($delivery->status === 'delivered') bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ str_replace('_', ' ', ucfirst($delivery->status)) }}
                                </span>
                            </dd>
                        </div>
                        @if ($delivery->notes)
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Notas</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $delivery->notes }}</dd>
                        </div>
                        @endif
                    </dl>

                    @if (in_array($delivery->status, ['assigned', 'in_transit']))
                        <h3 class="mt-6 font-semibold text-lg">Actualizar estado</h3>

                        @if ($delivery->status === 'assigned')
                            <form action="{{ route('repartidor.deliveries.update-status', $delivery) }}" method="POST" class="mt-4">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_transit">
                                <x-primary-button class="bg-yellow-500 hover:bg-yellow-600">Marcar como En Camino</x-primary-button>
                            </form>
                        @endif

                        @if ($delivery->status === 'in_transit')
                            <form action="{{ route('repartidor.deliveries.update-status', $delivery) }}" method="POST" class="mt-4 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <input type="hidden" name="status" value="delivered">
                                    <x-primary-button class="bg-green-600 hover:bg-green-700">Marcar como Entregado</x-primary-button>
                                </div>
                            </form>

                            <form action="{{ route('repartidor.deliveries.update-status', $delivery) }}" method="POST" class="mt-4 space-y-4">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="failed">
                                <div>
                                    <x-input-label for="failure_reason" value="Motivo del fallo" />
                                    <textarea id="failure_reason" name="failure_reason" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required></textarea>
                                    <x-input-error :messages="$errors->get('failure_reason')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="notes" value="Notas (opcional)" />
                                    <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                                </div>
                                <x-primary-button class="bg-red-600 hover:bg-red-700">Marcar como Fallido</x-primary-button>
                            </form>
                        @endif
                    @endif

                    @if (in_array($delivery->status, ['delivered', 'failed']))
                        <div class="mt-4 p-3 bg-gray-50 rounded text-sm text-gray-600">
                            Esta entrega ya fue {{ $delivery->status === 'delivered' ? 'completada' : 'marcada como fallida' }}.
                        </div>
                    @endif

                    @if ($delivery->failure_reason)
                        <div class="mt-4">
                            <h4 class="font-semibold text-sm text-red-600">Motivo de fallo:</h4>
                            <p class="text-sm text-gray-700">{{ $delivery->failure_reason }}</p>
                        </div>
                    @endif

                    @if ($delivery->statusLogs->count() > 0)
                        <h3 class="mt-6 font-semibold text-lg">Historial</h3>
                        <div class="mt-2 space-y-2 text-sm">
                            @foreach ($delivery->statusLogs as $log)
                                <div>
                                    <span class="font-medium">{{ str_replace('_', ' ', ucfirst($log->new_status)) }}</span>
                                    <span class="text-gray-500">— {{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('repartidor.deliveries.index') }}" class="text-gray-600 hover:text-gray-900">&larr; Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
