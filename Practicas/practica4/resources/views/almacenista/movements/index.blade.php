<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Movimientos de Inventario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-4">
                <a href="{{ route('almacenista.movements.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Nuevo Movimiento
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Producto</th>
                                <th class="text-left py-2">Tipo</th>
                                <th class="text-left py-2">Cantidad</th>
                                <th class="text-left py-2">Notas</th>
                                <th class="text-left py-2">Registrado por</th>
                                <th class="text-left py-2">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $movement)
                                <tr class="border-b">
                                    <td class="py-2">{{ $movement->product->name }}</td>
                                    <td class="py-2">
                                        @if ($movement->type === 'in')
                                            <span class="text-green-600 font-semibold">Entrada</span>
                                        @else
                                            <span class="text-red-600 font-semibold">Salida</span>
                                        @endif
                                    </td>
                                    <td class="py-2">{{ $movement->quantity }}</td>
                                    <td class="py-2">{{ $movement->notes ?? '-' }}</td>
                                    <td class="py-2">{{ $movement->user->name }}</td>
                                    <td class="py-2">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $movements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
