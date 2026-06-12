<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Inventory\Pages\EditInventory;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed([
            RoleAndPermissionSeeder::class,
            AttributeSeeder::class,
            CategorySeeder::class,
        ]);

        $user = User::factory()->create();
        $user->assignRole(UserRole::SuperAdmin->value);

        return $user;
    }

    public function test_guest_is_redirected_from_panel(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_dashboard_with_widgets(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_list_products(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create(['product_variant_id' => $variant->id, 'quantity' => 5]);

        $this->actingAs($admin)
            ->get('/admin/products')
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_admin_can_open_create_and_edit_product(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();

        $this->actingAs($admin)->get('/admin/products/create')->assertOk();
        $this->actingAs($admin)->get("/admin/products/{$product->id}/edit")->assertOk();
    }

    private function order(): Order
    {
        $order = Order::create([
            'order_number' => 'RS-TEST-001',
            'status' => OrderStatus::Pending->value,
            'payment_status' => 'pending',
            'payment_method' => 'whatsapp',
            'subtotal' => 100000,
            'grand_total' => 100000,
            'customer_name' => 'María Pérez',
            'customer_phone' => '573001112233',
            'customer_email' => 'maria@example.com',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Top Deportivo Pro',
            'variant_label' => 'M / Negro',
            'unit_price' => 50000,
            'quantity' => 2,
            'line_total' => 100000,
        ]);

        return $order;
    }

    public function test_admin_can_list_and_view_orders(): void
    {
        $admin = $this->admin();
        $order = $this->order();

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('María Pérez');

        $this->actingAs($admin)
            ->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Top Deportivo Pro');
    }

    public function test_admin_can_change_order_status(): void
    {
        $admin = $this->admin();
        $order = $this->order();

        Livewire::actingAs($admin)
            ->test(EditOrder::class, ['record' => $order->id])
            ->fillForm([
                'status' => OrderStatus::Shipped->value,
                'payment_status' => 'paid',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(OrderStatus::Shipped, $order->refresh()->status);
        $this->assertSame('paid', $order->payment_status->value);
    }

    public function test_admin_can_list_inventory(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create(['name' => 'Leggings Flow']);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'reserved' => 0,
            'low_stock_threshold' => 3,
        ]);

        $this->actingAs($admin)
            ->get('/admin/inventory')
            ->assertOk()
            ->assertSee('Leggings Flow');
    }

    public function test_admin_can_adjust_stock(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $inventory = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'reserved' => 0,
            'low_stock_threshold' => 3,
        ]);

        Livewire::actingAs($admin)
            ->test(EditInventory::class, ['record' => $inventory->id])
            ->fillForm([
                'quantity' => 25,
                'low_stock_threshold' => 5,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $inventory->refresh();
        $this->assertSame(25, $inventory->quantity);
        $this->assertSame(5, $inventory->low_stock_threshold);
    }

    public function test_admin_can_list_and_create_category(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create(['name' => 'Tops Deportivos']);

        $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk()
            ->assertSee('Tops Deportivos');

        Livewire::actingAs($admin)
            ->test(CreateCategory::class)
            ->fillForm([
                'name' => 'Conjuntos',
                'slug' => 'conjuntos',
                'gender' => 'mujer',
                'position' => 0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['slug' => 'conjuntos', 'name' => 'Conjuntos']);
    }

    public function test_customer_cannot_access_panel(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(UserRole::Customer->value);

        $this->actingAs($customer)->get('/admin')->assertForbidden();
    }
}
