<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:gestionar roles'),
        ];
    }

    public function index(): View
    {
        $roles = Role::where('guard_name', 'web')->paginate(15);
        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::where('guard_name', 'web')->get();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,NULL,id,guard_name,web',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions(
                Permission::whereIn('id', $validated['permissions'])->where('guard_name', 'web')->get()
            );
        }

        // [JUSTIFICACIÓN] Refrescar la caché inmediatamente después de modificar
        // permisos garantiza que los cambios surtan efecto en tiempo real.
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente.');
    }

    public function edit(Role $role): View|RedirectResponse
    {
        if ($role->guard_name !== 'web') {
            abort(404);
        }

        $permissions = Permission::where('guard_name', 'web')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->guard_name !== 'web') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id . ',id,guard_name,web',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions(
                Permission::whereIn('id', $validated['permissions'])->where('guard_name', 'web')->get()
            );
        } else {
            $role->syncPermissions([]);
        }

        // [JUSTIFICACIÓN] Refrescar la caché inmediatamente después de modificar
        // permisos garantiza que los cambios surtan efecto en tiempo real.
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado exitosamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // [JUSTIFICACIÓN] El rol admin no puede eliminarse porque es el rol de
        // máximo privilegio del sistema; su eliminación dejaría el sistema sin
        // capacidad de gestión.
        if ($role->name === 'admin') {
            abort(403, 'El rol admin no puede ser eliminado.');
        }

        if ($role->guard_name !== 'web') {
            abort(404);
        }

        $role->delete();

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}
