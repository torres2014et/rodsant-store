<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Formato monetario centralizado para toda la tienda.
 *
 * Por defecto `format()` muestra el precio en la moneda elegida por el
 * visitante (ver [[Currency]]); `base()` siempre formatea en COP (moneda real
 * del pedido, usada en el mensaje de WhatsApp hacia la tienda).
 */
final class Money
{
    /**
     * Formatea un monto en COP a la moneda de visualización activa.
     */
    public static function format(float|int|string|null $amount): string
    {
        return Currency::format($amount);
    }

    /**
     * Formatea siempre en COP (moneda base), sin conversión.
     */
    public static function base(float|int|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return config('rodsant.currency.symbol', '$')
            .number_format($value, (int) config('rodsant.currency.decimals', 0), ',', '.');
    }
}
