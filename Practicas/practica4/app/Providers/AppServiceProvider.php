<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\DispatchOrder;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\DeliveryPolicy;
use App\Policies\DispatchOrderPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\ProductPolicy;
use App\Policies\StockEntryPolicy;
use App\Policies\UserPolicy;
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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(InventoryMovement::class, InventoryMovementPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(StockEntry::class, StockEntryPolicy::class);
        Gate::policy(DispatchOrder::class, DispatchOrderPolicy::class);
        Gate::policy(Delivery::class, DeliveryPolicy::class);
    }
}
