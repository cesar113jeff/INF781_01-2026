<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\WarehouseSeeder::class);
    }

    public function test_admin_has_all_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(\Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'web')->first());

        $this->assertTrue($admin->can('ver productos'));
        $this->assertTrue($admin->can('crear productos'));
        $this->assertTrue($admin->can('editar productos'));
        $this->assertTrue($admin->can('eliminar productos'));
        $this->assertTrue($admin->can('registrar movimiento'));
        $this->assertTrue($admin->can('aprobar movimiento'));
        $this->assertTrue($admin->can('gestionar roles'));
    }

    public function test_supervisor_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(\Spatie\Permission\Models\Role::where('name', 'supervisor')->where('guard_name', 'web')->first());

        $this->assertTrue($user->can('ver productos'));
        $this->assertTrue($user->can('crear productos'));
        $this->assertTrue($user->can('aprobar movimiento'));
        $this->assertFalse($user->can('eliminar productos'));
        $this->assertFalse($user->can('gestionar roles'));
    }

    public function test_almacenista_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(\Spatie\Permission\Models\Role::where('name', 'almacenista')->where('guard_name', 'web')->first());

        $this->assertTrue($user->can('ver productos'));
        $this->assertTrue($user->can('registrar movimiento'));
        $this->assertFalse($user->can('crear productos'));
        $this->assertFalse($user->can('editar productos'));
        $this->assertFalse($user->can('eliminar productos'));
        $this->assertFalse($user->can('aprobar movimiento'));
        $this->assertFalse($user->can('gestionar roles'));
    }

    public function test_repartidor_api_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole(\Spatie\Permission\Models\Role::where('name', 'repartidor')->where('guard_name', 'api')->first());

        $this->assertTrue($user->hasPermissionTo('ver productos', 'api'));
        $this->assertTrue($user->hasPermissionTo('confirmar entrega', 'api'));
        $this->assertFalse($user->can('ver productos'));
        $this->assertFalse($user->can('crear productos'));
    }

    public function test_product_controller_middleware(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(\Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'web')->first());

        $this->actingAs($admin);
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();

        $almacenista = User::factory()->create();
        $almacenista->assignRole(\Spatie\Permission\Models\Role::where('name', 'almacenista')->where('guard_name', 'web')->first());
        $this->actingAs($almacenista);
        $this->get(route('products.create'))->assertForbidden();
    }

    public function test_role_controller_admin_protection(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(\Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'web')->first());
        $this->actingAs($admin);

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        $this->delete(route('roles.destroy', $adminRole))->assertForbidden();
    }

    public function test_movement_policy_almacenista_warehouse(): void
    {
        $warehouse1 = Warehouse::factory()->create(['code' => 'WH-T1']);
        $warehouse2 = Warehouse::factory()->create(['code' => 'WH-T2']);

        $almacenista = User::factory()->create(['warehouse_id' => $warehouse1->id]);
        $almacenista->assignRole(\Spatie\Permission\Models\Role::where('name', 'almacenista')->where('guard_name', 'web')->first());

        $product = Product::factory()->create();

        $movementSame = Movement::factory()->make([
            'warehouse_id' => $warehouse1->id,
            'product_id' => $product->id,
            'user_id' => $almacenista->id,
        ]);

        $movementDiff = Movement::factory()->make([
            'warehouse_id' => $warehouse2->id,
            'product_id' => $product->id,
            'user_id' => $almacenista->id,
        ]);

        $this->assertTrue($almacenista->can('create', $movementSame));
        $this->assertFalse($almacenista->can('create', $movementDiff));
    }

    public function test_api_products_endpoint(): void
    {
        $repartidor = User::factory()->create();
        $repartidor->assignRole(\Spatie\Permission\Models\Role::where('name', 'repartidor')->where('guard_name', 'api')->first());

        Product::factory()->count(3)->create();

        $response = $this->actingAs($repartidor, 'api')
            ->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_admin_role_not_deletable(): void
    {
        $this->assertDatabaseHas('roles', ['name' => 'admin']);

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        $this->assertNotNull($adminRole);

        $this->assertEquals('admin', $adminRole->name);
    }

    public function test_api_requires_auth(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_web_requires_auth(): void
    {
        $this->get(route('products.index'))->assertRedirectToRoute('login');
        $this->get(route('movements.index'))->assertRedirectToRoute('login');
        $this->get(route('roles.index'))->assertRedirectToRoute('login');
    }

    public function test_repartidor_api_guard_isolation(): void
    {
        $repartidor = User::factory()->create();
        $repartidor->assignRole(\Spatie\Permission\Models\Role::where('name', 'repartidor')->where('guard_name', 'api')->first());

        $this->assertFalse($repartidor->can('crear productos'));
        $this->assertFalse($repartidor->can('eliminar productos'));
        $this->assertFalse($repartidor->can('gestionar roles'));

        $this->assertTrue($repartidor->hasPermissionTo('ver productos', 'api'));
        $this->assertTrue($repartidor->hasPermissionTo('confirmar entrega', 'api'));
    }

    public function test_almacenista_cannot_approve_movements(): void
    {
        $warehouse = Warehouse::first() ?? Warehouse::factory()->create();
        $almacenista = User::factory()->create(['warehouse_id' => $warehouse->id]);
        $almacenista->assignRole(\Spatie\Permission\Models\Role::where('name', 'almacenista')->where('guard_name', 'web')->first());

        $this->assertFalse($almacenista->can('aprobar movimiento'));

        $product = Product::factory()->create();
        $movement = Movement::factory()->make([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $this->assertFalse($almacenista->can('approve', $movement));
    }

    public function test_repartidor_cannot_access_web_routes(): void
    {
        $repartidor = User::factory()->create();
        $repartidor->assignRole(\Spatie\Permission\Models\Role::where('name', 'repartidor')->where('guard_name', 'api')->first());

        $this->actingAs($repartidor);
        $this->get(route('products.index'))->assertForbidden();
        $this->get(route('movements.index'))->assertForbidden();
        $this->get(route('roles.index'))->assertForbidden();
    }

    public function test_almacenista_http_approve_returns_403(): void
    {
        $warehouse = Warehouse::first() ?? Warehouse::factory()->create();
        $almacenista = User::factory()->create(['warehouse_id' => $warehouse->id]);
        $almacenista->assignRole(\Spatie\Permission\Models\Role::where('name', 'almacenista')->where('guard_name', 'web')->first());

        $product = Product::factory()->create();
        $movement = Movement::factory()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'pending',
        ]);

        $this->actingAs($almacenista);
        $this->post(route('movements.approve', $movement))->assertForbidden();
    }

    public function test_admin_cannot_delete_admin_http(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(\Spatie\Permission\Models\Role::where('name', 'admin')->where('guard_name', 'web')->first());

        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();

        $this->actingAs($admin);
        $this->delete(route('roles.destroy', $adminRole))->assertForbidden();
    }
}
