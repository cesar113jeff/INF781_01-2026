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
                            <dt class="text-sm font-medium text-gray-500">Repartidor</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $delivery->repartidor->name }}</dd>
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
                    </dl>

                    @if ($delivery->statusLogs->count() > 0)
                        <h3 class="mt-6 font-semibold text-lg">Historial de cambios</h3>
                        <div class="mt-2 space-y-3">
                            @foreach ($delivery->statusLogs as $log)
                                <div class="text-sm">
                                    <span class="font-medium">{{ str_replace('_', ' ', ucfirst($log->new_status)) }}</span>
                                    por {{ $log->changer->name }}
                                    <span class="text-gray-500">— {{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('supervisor.deliveries.index') }}" class="text-gray-600 hover:text-gray-900">&larr; Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
