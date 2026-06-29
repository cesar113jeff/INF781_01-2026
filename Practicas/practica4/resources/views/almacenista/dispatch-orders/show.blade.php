<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Orden de Despacho #{{ $dispatchOrder->id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <dl class="divide-y divide-gray-200">
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Estado</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($dispatchOrder->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($dispatchOrder->status === 'approved') bg-green-100 text-green-700
                                    @else bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst($dispatchOrder->status) }}
                                </span>
                            </dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Destino</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->destination ?? '—' }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Notas</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->notes ?? '—' }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->user->name }}</dd>
                        </div>
                        @if ($dispatchOrder->status !== 'pending')
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Aprobado/Rechazado por</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->approver?->name ?? '—' }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Fecha de revisión</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->approved_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                            @if ($dispatchOrder->status === 'rejected')
                            <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                                <dt class="text-sm font-medium text-gray-500">Motivo de rechazo</dt>
                                <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-red-600">{{ $dispatchOrder->rejection_reason }}</dd>
                            </div>
                            @endif
                        @endif
                    </dl>

                    <h3 class="mt-6 font-semibold text-lg">Productos</h3>
                    <table class="min-w-full mt-2 divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Producto</th>
                                <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider py-2">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($dispatchOrder->items as $item)
                                <tr>
                                    <td class="py-2 text-sm">{{ $item->product->name }}</td>
                                    <td class="py-2 text-sm">{{ $item->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-6">
                        <a href="{{ route('almacenista.dispatch-orders.index') }}" class="text-gray-600 hover:text-gray-900">&larr; Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
