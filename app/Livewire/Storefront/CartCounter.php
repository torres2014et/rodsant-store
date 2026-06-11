<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Services\Cart\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Indicador del carrito en el header (icono + contador reactivo).
 */
class CartCounter extends Component
{
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->count = $cart->itemCount();
    }

    #[On('cart-updated')]
    public function refresh(CartService $cart): void
    {
        $this->count = $cart->itemCount();
    }

    public function render(): View
    {
        return view('livewire.storefront.cart-counter');
    }
}
