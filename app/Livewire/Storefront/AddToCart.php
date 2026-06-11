<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Selección de variante (talla/color) y alta en el carrito desde una tarjeta.
 *
 * Recibe el producto ya cargado con variantes/atributos/inventario, así que no
 * dispara consultas extra al montar (evita N+1 en listados).
 */
class AddToCart extends Component
{
    public int $productId;

    public string $productName;

    /** @var list<array{value:string}> */
    public array $sizes = [];

    /** @var list<array{value:string, hex:?string}> */
    public array $colors = [];

    /** @var array<string, array{id:int, available:int}> */
    public array $variantMap = [];

    public bool $hasVariants = true;

    public ?string $size = null;

    public ?string $color = null;

    public int $quantity = 1;

    public bool $added = false;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
        $this->productName = $product->name;

        $sizes = [];
        $colors = [];

        foreach ($product->variants as $variant) {
            if (! $variant->is_active) {
                continue;
            }

            $size = null;
            $color = null;
            $hex = null;

            foreach ($variant->attributeValues as $value) {
                $slug = $value->attribute?->slug;
                if ($slug === 'talla') {
                    $size = $value->value;
                } elseif ($slug === 'color') {
                    $color = $value->value;
                    $hex = $value->hex();
                }
            }

            if ($size !== null && ! isset($sizes[$size])) {
                $sizes[$size] = ['value' => $size];
            }
            if ($color !== null && ! isset($colors[$color])) {
                $colors[$color] = ['value' => $color, 'hex' => $hex];
            }

            $this->variantMap[$this->key($color, $size)] = [
                'id' => $variant->id,
                'available' => $variant->availableStock(),
            ];
        }

        $this->sizes = array_values($sizes);
        $this->colors = array_values($colors);
        $this->hasVariants = $this->sizes !== [] || $this->colors !== [];

        // Producto sin atributos: una sola variante directa.
        if (! $this->hasVariants && $product->variants->isNotEmpty()) {
            $only = $product->variants->firstWhere('is_active', true);
            if ($only !== null) {
                $this->variantMap[$this->key(null, null)] = [
                    'id' => $only->id,
                    'available' => $only->availableStock(),
                ];
            }
        }
    }

    public function selectSize(string $size): void
    {
        $this->size = $this->size === $size ? null : $size;
        $this->added = false;
        $this->resetErrorBag();
    }

    public function selectColor(string $color): void
    {
        $this->color = $this->color === $color ? null : $color;
        $this->added = false;
        $this->resetErrorBag();
    }

    public function addToCart(CartService $cart): void
    {
        if (! empty($this->sizes) && $this->size === null) {
            $this->addError('variant', 'Elige una talla.');

            return;
        }

        if (! empty($this->colors) && $this->color === null) {
            $this->addError('variant', 'Elige un color.');

            return;
        }

        $selected = $this->variantMap[$this->key($this->color, $this->size)] ?? null;

        if ($selected === null) {
            $this->addError('variant', 'Esa combinación no está disponible.');

            return;
        }

        try {
            $cart->add($selected['id'], max(1, $this->quantity));
        } catch (CartException $e) {
            $this->addError('variant', $e->getMessage());

            return;
        }

        $this->added = true;
        $this->quantity = 1;
        $this->dispatch('cart-updated');
        $this->dispatch('cart-item-added', name: $this->productName);
    }

    public function render(): View
    {
        return view('livewire.storefront.add-to-cart');
    }

    private function key(?string $color, ?string $size): string
    {
        return ($color ?? '').'|'.($size ?? '');
    }
}
