<?php

namespace Database\Factories;

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovementFactory extends Factory
{
    protected $model = Movement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'type' => fake()->randomElement(['entry', 'exit']),
            'quantity' => fake()->numberBetween(1, 50),
            'warehouse_id' => Warehouse::factory(),
            'user_id' => User::factory(),
            'status' => 'pending',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
