@extends('layouts.app')

@section('title', 'Movimientos')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Movimientos</h1>
        @can('registrar movimiento')
            <a href="{{ route('movements.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Registrar Movimiento
            </a>
        @endcan
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-center">Tipo</th>
                    <th class="px-4 py-2 text-right">Cantidad</th>
                    <th class="px-4 py-2 text-left">Almacén</th>
                    <th class="px-4 py-2 text-center">Estado</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr class="border-t">
                        <td class="px-4 py-2">#{{ $movement->id }}</td>
                        <td class="px-4 py-2">{{ $movement->product->name }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded text-xs {{ $movement->type === 'entry' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $movement->type === 'entry' ? 'Entrada' : 'Salida' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right">{{ $movement->quantity }}</td>
                        <td class="px-4 py-2">{{ $movement->warehouse->name }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 rounded text-xs {{ $movement->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $movement->status === 'approved' ? 'Aprobado' : 'Pendiente' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            @can('aprobar movimiento')
                                @if ($movement->status === 'pending')
                                    <form action="{{ route('movements.approve', $movement) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:underline">
                                            Aprobar
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">
                            No hay movimientos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $movements->links() }}
    </div>
@endsection
