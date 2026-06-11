<?php

declare(strict_types=1);

namespace App\Livewire\Storefront\Checkout;

use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Resumen del pedido (líneas + totales) mostrado en el checkout.
 * Se refresca al cambiar el carrito.
 */
class OrderSummary extends Component
{
    #[On('cart-updated')]
    public function refresh(): void
    {
        // El render() vuelve a leer el carrito; este método fuerza el ciclo.
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
                    'label' => $variant?->label(),
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                    'image' => $path
                        ? (Str::startsWith($path, ['http://', 'https://']) ? $path : Storage::url($path))
                        : null,
                ];
            }
        }

        return view('livewire.storefront.checkout.order-summary', [
            'items' => $items,
            'subtotal' => $current?->subtotal() ?? 0.0,
            'count' => $current?->totalQuantity() ?? 0,
        ]);
    }
}
