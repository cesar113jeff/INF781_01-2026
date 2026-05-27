<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::firstOrCreate(
            ['code' => 'WH-001'],
            [
                'name' => 'Almacén Central',
                'description' => 'Almacén principal de AlmaTrack S.R.L.',
            ]
        );
    }
}
