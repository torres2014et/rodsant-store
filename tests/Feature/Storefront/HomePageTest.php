<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    private function seedStore(): void
    {
        $this->seed([
            AttributeSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
        ]);
    }

    public function test_home_renders_with_core_sections(): void
    {
        $this->seedStore();

        $category = Category::query()->first();

        Product::factory()->count(4)->create([
            'category_id' => $category->id,
            'is_active' => true,
            'is_featured' => true,
            'is_new' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Compra por categoría')
            ->assertSee('Esenciales de entrenamiento')
            ->assertSee('Síguenos en Instagram')
            ->assertSee('Todos los derechos reservados', false);
    }

    public function test_home_renders_without_products(): void
    {
        $this->seedStore();

        // Sin productos ni banners el Home no debe romperse (hero por defecto).
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Explorar la tienda');
    }

    public function test_home_exposes_seo_metadata(): void
    {
        $this->seedStore();

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('property="og:title"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('rel="canonical"', false);
    }

    public function test_product_card_shows_sale_badge_and_price(): void
    {
        $this->seedStore();

        $category = Category::query()->first();
        $product = Product::factory()->featured()->onSale()->create([
            'category_id' => $category->id,
            'name' => 'Hoodie Editorial Noir',
        ]);

        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create(['product_variant_id' => $variant->id, 'quantity' => 5]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Hoodie Editorial Noir')
            ->assertSee($product->price_formatted);
    }

    public function test_navigation_stub_routes_resolve(): void
    {
        $this->seedStore();
        $category = Category::query()->first();

        $this->get(route('catalog.index'))->assertOk();
        $this->get(route('catalog.new'))->assertOk();
        $this->get(route('category.show', $category->slug))->assertOk()->assertSee($category->name);
        $this->get(route('account.index'))->assertOk();
        $this->get(route('wishlist.index'))->assertOk();
    }
}
