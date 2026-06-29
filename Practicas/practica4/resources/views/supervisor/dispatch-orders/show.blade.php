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
                            <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->user->name }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Destino</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->destination ?? '—' }}</dd>
                        </div>
                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">Notas</dt>
                            <dd class="mt-1 sm:mt-0 sm:col-span-2 text-sm text-gray-900">{{ $dispatchOrder->notes ?? '—' }}</dd>
                        </div>
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

                    @if ($dispatchOrder->status === 'pending')
                        <div class="mt-6 flex gap-4">
                            <form action="{{ route('supervisor.dispatch-orders.approve', $dispatchOrder) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <x-primary-button class="bg-green-600 hover:bg-green-700">Aprobar</x-primary-button>
                            </form>
                            <button type="button" onclick="showRejectForm()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Rechazar</button>
                        </div>

                        <form id="reject-form" action="{{ route('supervisor.dispatch-orders.reject', $dispatchOrder) }}" method="POST" class="mt-4 hidden space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <x-input-label for="rejection_reason" value="Motivo de rechazo" />
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required></textarea>
                                <x-input-error :messages="$errors->get('rejection_reason')" class="mt-2" />
                            </div>
                            <x-primary-button class="bg-red-600 hover:bg-red-700">Confirmar Rechazo</x-primary-button>
                        </form>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('supervisor.dispatch-orders.index') }}" class="text-gray-600 hover:text-gray-900">&larr; Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function showRejectForm() {
        document.getElementById('reject-form').classList.remove('hidden');
    }
</script>
