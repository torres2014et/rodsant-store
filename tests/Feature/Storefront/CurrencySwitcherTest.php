<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Livewire\Storefront\CurrencySwitcher;
use App\Support\Currency;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CurrencySwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_currency_is_cop(): void
    {
        $this->assertSame('COP', Currency::current());
        $this->assertSame('$100.000', Money::format(100000));
    }

    public function test_switching_to_usd_converts_prices(): void
    {
        Livewire::test(CurrencySwitcher::class)
            ->call('select', 'USD')
            ->assertSet('current', 'USD');

        $this->assertSame('USD', Currency::current());
        // 100.000 COP * 0,00025 = 25,00 USD
        $this->assertSame('US$25,00', Money::format(100000));
    }

    public function test_base_format_always_stays_in_cop(): void
    {
        Currency::set('EUR');

        // El display cambia…
        $this->assertSame('€', Currency::meta()['symbol']);
        // …pero base() (pedido / WhatsApp) sigue en COP.
        $this->assertSame('$100.000', Money::base(100000));
    }

    public function test_invalid_currency_falls_back_to_cop(): void
    {
        Currency::set('XYZ');

        $this->assertSame('COP', Currency::current());
    }
}
