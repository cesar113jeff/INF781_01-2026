<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // ===== Permisos GUARD WEB =====
        $webPermissionNames = [
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',
            'registrar movimiento',
            'aprobar movimiento',
            'gestionar roles',
        ];

        foreach ($webPermissionNames as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // ===== Permisos GUARD API =====
        $apiPermissionNames = [
            'ver productos',
            'confirmar entrega',
        ];

        foreach ($apiPermissionNames as $name) {
            Permission::findOrCreate($name, 'api');
        }

        // Refresh the cache so givePermissionTo can find the newly created permissions
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // ===== Rol ADMIN (web) =====
        $admin = Role::findOrCreate('admin', 'web');
        $admin->givePermissionTo(Permission::where('guard_name', 'web')->get());

        // ===== Rol SUPERVISOR (web) =====
        $supervisor = Role::findOrCreate('supervisor', 'web');
        $supervisor->givePermissionTo(
            Permission::where('guard_name', 'web')
                ->whereNotIn('name', ['eliminar productos', 'gestionar roles'])
                ->get()
        );

        // ===== Rol ALMACENISTA (web) =====
        $almacenista = Role::findOrCreate('almacenista', 'web');
        $almacenista->givePermissionTo(
            Permission::where('guard_name', 'web')
                ->whereIn('name', ['ver productos', 'registrar movimiento'])
                ->get()
        );

        // ===== Rol REPARTIDOR (api) =====
        $repartidor = Role::findOrCreate('repartidor', 'api');
        $repartidor->givePermissionTo(
            Permission::where('guard_name', 'api')->get()
        );
    }
}
