<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Support\Currency;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Selector flotante de moneda de visualización (COP, USD, EUR, PAB).
 * Guarda la elección en sesión y recarga para refrescar todos los precios.
 */
class CurrencySwitcher extends Component
{
    public string $current = Currency::BASE;

    public function mount(): void
    {
        $this->current = Currency::current();
    }

    public function select(string $code): void
    {
        Currency::set($code);
        $this->current = Currency::current();
        $this->dispatch('currency-changed');
        $this->js('window.location.reload()');
    }

    public function render(): View
    {
        return view('livewire.storefront.currency-switcher', [
            'currencies' => Currency::all(),
            'meta' => Currency::meta($this->current),
        ]);
    }
}
