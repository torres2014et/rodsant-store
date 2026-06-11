<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Support\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Bloque interactivo de la ficha de producto: galería + selección de
 * talla/color + cantidad + añadir al carrito (con validación de stock).
 *
 * Recibe el producto ya cargado (variantes/atributos/inventario/imágenes)
 * para no disparar consultas extra al montar.
 */
class ProductDetail extends Component
{
    public int $productId;

    public string $productName;

    public ?string $categoryName = null;

    public ?string $categorySlug = null;

    public string $priceFormatted = '';

    public ?string $basePriceFormatted = null;

    public bool $isOnSale = false;

    public int $discountPct = 0;

    /** @var list<array{url: string, alt: string}> */
    public array $images = [];

    /** @var list<array{value: string}> */
    public array $sizes = [];

    /** @var list<array{value: string, hex: string|null}> */
    public array $colors = [];

    /** @var array<string, array{id: int, available: int}> */
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
        $this->categoryName = $product->category?->name;
        $this->categorySlug = $product->category?->slug;
        $this->priceFormatted = $product->price_formatted;
        $this->isOnSale = $product->isOnSale();
        $this->basePriceFormatted = $this->isOnSale ? Money::format($product->base_price) : null;
        $this->discountPct = $product->discount_percentage;

        $this->images = $product->images
            ->map(fn ($image): array => [
                'url' => $this->resolveImage($image->path),
                'alt' => $image->alt ?? $product->name,
            ])
            ->filter(fn (array $image): bool => $image['url'] !== '')
            ->values()
            ->all();

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
        $this->quantity = 1;
        $this->added = false;
        $this->resetErrorBag();
    }

    public function selectColor(string $color): void
    {
        $this->color = $this->color === $color ? null : $color;
        $this->quantity = 1;
        $this->added = false;
        $this->resetErrorBag();
    }

    public function increment(): void
    {
        $available = $this->selectedAvailable();
        if ($available === null || $this->quantity < $available) {
            $this->quantity++;
        }
    }

    public function decrement(): void
    {
        $this->quantity = max(1, $this->quantity - 1);
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
        $this->dispatch('cart-updated');
        $this->dispatch('cart-item-added', name: $this->productName);
    }

    /**
     * Stock disponible para la selección actual (null si está incompleta).
     */
    public function selectedAvailable(): ?int
    {
        if (! empty($this->sizes) && $this->size === null) {
            return null;
        }
        if (! empty($this->colors) && $this->color === null) {
            return null;
        }

        return $this->variantMap[$this->key($this->color, $this->size)]['available'] ?? 0;
    }

    public function render(): View
    {
        return view('livewire.storefront.product-detail', [
            'available' => $this->selectedAvailable(),
        ]);
    }

    private function resolveImage(?string $path): string
    {
        if (! $path) {
            return '';
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path);
    }

    private function key(?string $color, ?string $size): string
    {
        return ($color ?? '').'|'.($size ?? '');
    }
}
