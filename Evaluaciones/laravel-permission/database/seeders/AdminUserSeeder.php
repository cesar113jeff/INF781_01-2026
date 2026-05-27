<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::first();

        if (User::where('email', 'admin@almatrack.com')->exists()) {
            return;
        }

        $admin = User::factory()->create([
            'name' => 'Admin AlmaTrack',
            'email' => 'admin@almatrack.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole(Role::where('name', 'admin')->where('guard_name', 'web')->first());

        $supervisor = User::factory()->create([
            'name' => 'Supervisor AlmaTrack',
            'email' => 'supervisor@almatrack.com',
            'password' => bcrypt('password'),
        ]);
        $supervisor->assignRole(Role::where('name', 'supervisor')->where('guard_name', 'web')->first());

        $almacenista = User::factory()->create([
            'name' => 'Almacenista AlmaTrack',
            'email' => 'almacenista@almatrack.com',
            'password' => bcrypt('password'),
            'warehouse_id' => $warehouse?->id,
        ]);
        $almacenista->assignRole(Role::where('name', 'almacenista')->where('guard_name', 'web')->first());

        $repartidor = User::factory()->create([
            'name' => 'Repartidor AlmaTrack',
            'email' => 'repartidor@almatrack.com',
            'password' => bcrypt('password'),
        ]);
        $repartidor->assignRole(Role::where('name', 'repartidor')->where('guard_name', 'api')->first());
    }
}
