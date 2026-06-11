<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Exceptions\CartException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Gestiona el carrito del visitante (invitado) durante la sesión.
 *
 * El carrito se identifica por un token estable guardado en la sesión, de modo
 * que sobrevive a la navegación sin requerir cuenta. Toda la validación de
 * inventario (anti-sobreventa) vive aquí: disponible = quantity - reserved.
 */
class CartService
{
    private const SESSION_KEY = 'rodsant_cart_token';

    /**
     * Token de carrito de la sesión actual (se crea la primera vez).
     */
    public function token(): string
    {
        $token = Session::get(self::SESSION_KEY);

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();
            Session::put(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Carrito actual sin crearlo (para lecturas: contador, resumen).
     */
    public function currentOrNull(): ?Cart
    {
        $token = Session::get(self::SESSION_KEY);

        if (! is_string($token) || $token === '') {
            return null;
        }

        return Cart::query()
            ->where('session_id', $token)
            ->with($this->itemRelations())
            ->first();
    }

    /**
     * Carrito actual, creándolo si no existe (para escrituras).
     */
    public function current(): Cart
    {
        $cart = Cart::query()->firstOrCreate(['session_id' => $this->token()]);

        return $cart->load($this->itemRelations());
    }

    /**
     * Añade una variante al carrito validando disponibilidad real.
     *
     * @throws CartException
     */
    public function add(int $variantId, int $quantity = 1): CartItem
    {
        $quantity = max(1, $quantity);
        $variant = $this->resolveVariant($variantId);
        $cart = $this->current();

        $existing = $cart->items->firstWhere('product_variant_id', $variant->id);
        $desired = ($existing?->quantity ?? 0) + $quantity;

        $this->assertStock($variant, $desired);

        $item = CartItem::query()->updateOrCreate(
            ['cart_id' => $cart->id, 'product_variant_id' => $variant->id],
            ['quantity' => $desired, 'unit_price' => (float) $variant->price()],
        );

        $this->touchCart($cart);

        return $item;
    }

    /**
     * Fija la cantidad exacta de una línea (desde la página de carrito).
     *
     * @throws CartException
     */
    public function setQuantity(int $cartItemId, int $quantity): void
    {
        $cart = $this->current();
        $item = $cart->items->firstWhere('id', $cartItemId);

        if ($item === null) {
            return;
        }

        if ($quantity <= 0) {
            $this->remove($cartItemId);

            return;
        }

        $variant = $this->resolveVariant($item->product_variant_id);
        $this->assertStock($variant, $quantity);

        $item->update([
            'quantity' => $quantity,
            'unit_price' => (float) $variant->price(),
        ]);

        $this->touchCart($cart);
    }

    public function remove(int $cartItemId): void
    {
        $cart = $this->currentOrNull();

        $cart?->items()->whereKey($cartItemId)->delete();

        if ($cart !== null) {
            $this->touchCart($cart);
        }
    }

    public function clear(): void
    {
        $cart = $this->currentOrNull();

        if ($cart === null) {
            return;
        }

        $cart->items()->delete();
        $this->touchCart($cart);
    }

    public function itemCount(): int
    {
        return (int) ($this->currentOrNull()?->items->sum('quantity') ?? 0);
    }

    public function subtotal(): float
    {
        return (float) ($this->currentOrNull()?->subtotal() ?? 0.0);
    }

    public function isEmpty(): bool
    {
        return $this->itemCount() === 0;
    }

    /**
     * Revalida todo el carrito contra el inventario actual.
     * Devuelve la lista de problemas (vacía si todo OK).
     *
     * @return list<string>
     */
    public function validateAgainstStock(Cart $cart): array
    {
        $problems = [];

        foreach ($cart->items as $item) {
            $variant = $item->variant;

            if ($variant === null || ! $variant->is_active || ! ($variant->product?->is_active)) {
                $problems[] = 'Un producto de tu carrito ya no está disponible.';

                continue;
            }

            if ($variant->availableStock() < $item->quantity) {
                $name = $variant->product->name.' '.$variant->label();
                $problems[] = trim($name).': solo quedan '.$variant->availableStock().' unidades.';
            }
        }

        return $problems;
    }

    /**
     * Relaciones necesarias para pintar líneas del carrito.
     *
     * @return array<int, string>
     */
    private function itemRelations(): array
    {
        return [
            'items.variant.product.primaryImage',
            'items.variant.product:id,name,slug,is_active,base_price,sale_price',
            'items.variant.inventory',
            'items.variant.attributeValues.attribute:id,slug',
        ];
    }

    /**
     * @throws CartException
     */
    private function resolveVariant(int $variantId): ProductVariant
    {
        $variant = ProductVariant::query()
            ->with(['product', 'inventory', 'attributeValues.attribute'])
            ->find($variantId);

        if ($variant === null || ! $variant->is_active || ! ($variant->product?->is_active)) {
            throw CartException::unavailable('Producto');
        }

        return $variant;
    }

    /**
     * @throws CartException
     */
    private function assertStock(ProductVariant $variant, int $desiredQuantity): void
    {
        $available = $variant->availableStock();
        $name = trim($variant->product->name.' '.$variant->label());

        if ($available <= 0) {
            throw CartException::outOfStock($name);
        }

        if ($desiredQuantity > $available) {
            throw CartException::insufficientStock($name, $available);
        }
    }

    private function touchCart(Cart $cart): void
    {
        $cart->forceFill(['updated_at' => now()])->save();
        $cart->load($this->itemRelations());
    }
}
