<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_customer_cannot_access_panel(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $customer = User::factory()->create();
        $customer->assignRole(UserRole::Customer->value);

        $this->actingAs($customer)->get('/admin')->assertForbidden();
    }
}
