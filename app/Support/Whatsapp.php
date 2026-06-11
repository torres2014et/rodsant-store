<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Número y enlaces de WhatsApp de la tienda.
 *
 * El número se gestiona desde el panel (Setting `whatsapp_number`) y cae al
 * valor de configuración si no se ha definido. Fuente única de verdad para el
 * botón flotante, el cierre de pedidos y cualquier enlace wa.me.
 */
final class Whatsapp
{
    /**
     * Número de la tienda en formato wa.me (solo dígitos, con indicativo país).
     */
    public static function number(): string
    {
        $number = Setting::get('whatsapp_number', config('rodsant.whatsapp.number'));

        return (string) Str::of((string) $number)->replaceMatches('/\D+/', '');
    }

    /**
     * Indica si hay un número configurado y utilizable.
     */
    public static function isConfigured(): bool
    {
        return self::number() !== '';
    }

    /**
     * Enlace wa.me hacia la tienda, con mensaje opcional precargado.
     */
    public static function link(?string $text = null): string
    {
        $url = config('rodsant.whatsapp.base_url', 'https://wa.me/').self::number();

        if ($text !== null && $text !== '') {
            $url .= '?text='.rawurlencode($text);
        }

        return $url;
    }
}
