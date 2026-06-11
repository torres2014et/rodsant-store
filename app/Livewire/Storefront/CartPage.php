<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Exceptions\CartException;
use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront', ['title' => 'Tu carrito'])]
class CartPage extends Component
{
    public ?string $notice = null;

    public function increment(int $itemId, CartService $cart): void
    {
        $this->change($itemId, +1, $cart);
    }

    public function decrement(int $itemId, CartService $cart): void
    {
        $this->change($itemId, -1, $cart);
    }

    public function remove(int $itemId, CartService $cart): void
    {
        $cart->remove($itemId);
        $this->notice = null;
        $this->dispatch('cart-updated');
    }

    private function change(int $itemId, int $delta, CartService $cart): void
    {
        $this->notice = null;
        $current = $cart->currentOrNull();
        $item = $current?->items->firstWhere('id', $itemId);

        if ($item === null) {
            return;
        }

        try {
            $cart->setQuantity($itemId, $item->quantity + $delta);
        } catch (CartException $e) {
            $this->notice = $e->getMessage();
        }

        $this->dispatch('cart-updated');
    }

    public function render(CartService $cart): View
    {
        $current = $cart->currentOrNull();
        $items = [];

        if ($current !== null) {
            foreach ($current->items as $item) {
                $variant = $item->variant;
                $product = $variant?->product;
                $path = $product?->primaryImage?->path;

                $items[] = [
                    'id' => $item->id,
                    'name' => $product?->name ?? 'Producto',
                    'slug' => $product?->slug,
                    'label' => $variant?->label(),
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                    'available' => $variant?->availableStock() ?? 0,
                    'image' => $path
                        ? (Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path))
                        : null,
                ];
            }
        }

        return view('livewire.storefront.cart-page', [
            'items' => $items,
            'subtotal' => $current?->subtotal() ?? 0.0,
        ]);
    }
}
