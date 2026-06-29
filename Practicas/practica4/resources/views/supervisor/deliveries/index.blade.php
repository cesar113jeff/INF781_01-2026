<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Entregas</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receptor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Repartidor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($deliveries as $delivery)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $delivery->recipient_name }}</td>
                                    <td class="px-6 py-4 text-sm max-w-xs truncate">{{ $delivery->address }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $delivery->repartidor->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            @if($delivery->status === 'assigned') bg-blue-100 text-blue-700
                                            @elseif($delivery->status === 'in_transit') bg-yellow-100 text-yellow-700
                                            @elseif($delivery->status === 'delivered') bg-green-100 text-green-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            {{ str_replace('_', ' ', ucfirst($delivery->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('supervisor.deliveries.show', $delivery) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay entregas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $deliveries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
