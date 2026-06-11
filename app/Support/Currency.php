<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * Moneda de visualización de la tienda.
 *
 * La base de datos guarda los precios en COP (moneda base). Este helper
 * convierte y formatea a la moneda elegida por el visitante (selector
 * flotante), guardada en sesión. El cierre del pedido siempre es en COP.
 */
final class Currency
{
    private const SESSION_KEY = 'rodsant_currency';

    public const BASE = 'COP';

    /**
     * Código de la moneda activa (válida y soportada).
     */
    public static function current(): string
    {
        $code = Session::get(self::SESSION_KEY, self::BASE);

        return is_string($code) && array_key_exists($code, self::all()) ? $code : self::BASE;
    }

    public static function set(string $code): void
    {
        if (array_key_exists($code, self::all())) {
            Session::put(self::SESSION_KEY, $code);
        }
    }

    /**
     * @return array<string, array{label:string, symbol:string, flag:string, rate:float|int, decimals:int}>
     */
    public static function all(): array
    {
        return config('rodsant.currencies', [
            'COP' => ['label' => 'Colombia', 'symbol' => '$', 'flag' => '🇨🇴', 'rate' => 1, 'decimals' => 0],
        ]);
    }

    /**
     * @return array{label:string, symbol:string, flag:string, rate:float|int, decimals:int}
     */
    public static function meta(?string $code = null): array
    {
        $code ??= self::current();

        return self::all()[$code] ?? self::all()[self::BASE];
    }

    /**
     * Convierte un monto en COP a la moneda activa.
     */
    public static function convert(float|int|string|null $cop, ?string $code = null): float
    {
        $meta = self::meta($code);

        return (float) ($cop ?? 0) * (float) $meta['rate'];
    }

    /**
     * Formatea un monto en COP a la moneda activa, con su símbolo.
     */
    public static function format(float|int|string|null $cop, ?string $code = null): string
    {
        $meta = self::meta($code);
        $value = self::convert($cop, $code);

        return $meta['symbol'].number_format($value, (int) $meta['decimals'], ',', '.');
    }
}
