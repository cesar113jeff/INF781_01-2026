@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Roles</h1>
        <a href="{{ route('roles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Crear Rol
        </a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Guard</th>
                    <th class="px-4 py-2 text-left">Permisos</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr class="border-t">
                        <td class="px-4 py-2 font-medium">{{ $role->name }}</td>
                        <td class="px-4 py-2">{{ $role->guard_name }}</td>
                        <td class="px-4 py-2">
                            @foreach ($role->permissions as $permission)
                                <span class="inline-block bg-gray-100 text-xs px-2 py-1 rounded mr-1">
                                    {{ $permission->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('roles.edit', $role) }}" class="text-yellow-600 hover:underline mr-2">
                                Editar
                            </a>
                            @if ($role->name !== 'admin')
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿Eliminar este rol?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">
                            No hay roles registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $roles->links() }}
    </div>
@endsection
