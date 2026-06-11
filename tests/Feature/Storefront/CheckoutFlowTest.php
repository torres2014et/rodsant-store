<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Livewire\Storefront\AddToCart;
use App\Livewire\Storefront\Checkout\CheckoutForm;
use App\Livewire\Storefront\Checkout\WhatsappOrderGenerator;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            AttributeSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
        ]);
    }

    /**
     * Crea un producto con una variante Negro/M e inventario dado.
     *
     * @return array{0: Product, 1: ProductVariant}
     */
    private function makeProduct(int $stock = 5, int $price = 120000): array
    {
        $category = Category::query()->first();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hoodie Noir',
            'base_price' => $price,
            'sale_price' => null,
            'is_active' => true,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'HOODIE-NOIR-M',
            'is_active' => true,
        ]);

        $size = AttributeValue::query()->where('value', 'M')->first();
        $color = AttributeValue::query()->where('value', 'Negro')->first();
        $variant->attributeValues()->attach([$size->id, $color->id]);

        Inventory::query()->create([
            'product_variant_id' => $variant->id,
            'quantity' => $stock,
            'reserved' => 0,
            'low_stock_threshold' => 3,
        ]);

        return [$product->fresh(['variants.attributeValues.attribute', 'variants.inventory']), $variant];
    }

    private function cart(): CartService
    {
        return app(CartService::class);
    }

    public function test_add_to_cart_requires_variant_and_adds_item(): void
    {
        [$product] = $this->makeProduct();

        Livewire::test(AddToCart::class, ['product' => $product])
            ->call('addToCart')
            ->assertHasErrors('variant') // sin talla/color seleccionados
            ->call('selectSize', 'M')
            ->call('selectColor', 'Negro')
            ->set('quantity', 2)
            ->call('addToCart')
            ->assertHasNoErrors()
            ->assertSet('added', true)
            ->assertDispatched('cart-updated');

        $this->assertSame(2, $this->cart()->itemCount());
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        [$product] = $this->makeProduct(stock: 1);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->call('selectSize', 'M')
            ->call('selectColor', 'Negro')
            ->set('quantity', 3)
            ->call('addToCart')
            ->assertHasErrors('variant');

        $this->assertSame(0, $this->cart()->itemCount());
    }

    public function test_checkout_requires_mandatory_fields(): void
    {
        [, $variant] = $this->makeProduct();
        $this->cart()->add($variant->id, 1);

        Livewire::test(CheckoutForm::class)
            ->call('placeOrder')
            ->assertHasErrors(['full_name', 'phone', 'department', 'city', 'address_line']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_checkout_creates_order_reserves_stock_and_clears_cart(): void
    {
        [, $variant] = $this->makeProduct(stock: 5, price: 120000);
        $this->cart()->add($variant->id, 2);

        Livewire::test(CheckoutForm::class)
            ->set('full_name', 'Juan Pérez')
            ->set('phone', '300 123 4567')
            ->set('email', 'juan@example.com')
            ->set('department', 'Cundinamarca')
            ->set('city', 'Bogotá')
            ->set('address_line', 'Calle 100 # 10-20')
            ->set('references', 'Portería')
            ->set('notes', 'Entrega en la tarde')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect();

        $order = Order::query()->with('items')->first();
        $this->assertNotNull($order);
        $this->assertMatchesRegularExpression('/^RS-\d{4}-\d{6}$/', $order->order_number);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(PaymentMethod::Whatsapp, $order->payment_method);
        $this->assertSame('Juan Pérez', $order->customer_name);
        $this->assertEquals(240000, (float) $order->grand_total);

        // Snapshot inmutable en la línea.
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertSame('Hoodie Noir', $item->product_name);
        $this->assertSame(2, $item->quantity);
        $this->assertEquals(240000, (float) $item->line_total);

        // Inventario reservado y carrito vacío.
        $this->assertSame(2, Inventory::query()->where('product_variant_id', $variant->id)->value('reserved'));
        $this->assertSame(0, $this->cart()->itemCount());
    }

    public function test_confirmation_generates_whatsapp_link_and_marks_sent(): void
    {
        [, $variant] = $this->makeProduct();
        $this->cart()->add($variant->id, 1);

        Livewire::test(CheckoutForm::class)
            ->set('full_name', 'Ana Gómez')
            ->set('phone', '3001112222')
            ->set('department', 'Antioquia')
            ->set('city', 'Medellín')
            ->set('address_line', 'Carrera 70 # 1-2')
            ->call('placeOrder');

        $order = Order::query()->firstOrFail();
        $this->assertNull($order->whatsapp_sent_at);

        $component = Livewire::test(WhatsappOrderGenerator::class, ['order' => $order]);
        $component->assertSee($order->order_number);

        $url = $component->get('whatsappUrl');
        $this->assertStringContainsString('https://wa.me/', $url);
        $this->assertStringContainsString(rawurlencode($order->order_number), $url);

        $component->call('markSent')->assertSet('sent', true);
        $this->assertNotNull($order->fresh()->whatsapp_sent_at);
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        Livewire::test(CheckoutForm::class)->assertRedirect(route('cart.index'));
    }
}
