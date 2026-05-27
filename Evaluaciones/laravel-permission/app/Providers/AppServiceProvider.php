<?php

namespace App\Providers;

use App\Models\Movement;
use App\Policies\MovementPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // [JUSTIFICACIÓN] MovementPolicy se registra explícitamente mediante
        // Gate::policy() para ser utilizada con la función can() nativa de Laravel.
        Gate::policy(Movement::class, MovementPolicy::class);
    }
}
