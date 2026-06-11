<?php

declare(strict_types=1);

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\Setting;
use App\Support\Money;
use Illuminate\Support\Str;

/**
 * Construye el mensaje de cierre de pedido y el enlace wa.me hacia la tienda.
 */
class WhatsappMessageService
{
    /**
     * Mensaje profesional listo para enviar a la tienda por WhatsApp.
     */
    public function build(Order $order): string
    {
        $order->loadMissing(['items', 'shippingAddress']);

        $lines = [];
        $lines[] = "*Pedido:* {$order->order_number}";
        $lines[] = '';
        $lines[] = '*Cliente:*';
        $lines[] = $order->customer_name;

        if ($order->customer_phone !== null && $order->customer_phone !== '') {
            $lines[] = "Tel: {$order->customer_phone}";
        }

        $lines[] = '';
        $lines[] = '*Productos:*';

        foreach ($order->items as $item) {
            $name = trim($item->product_name.' '.($item->variant_label ?? ''));
            $lines[] = "• {$name} x{$item->quantity}  —  ".Money::base($item->line_total);
        }

        $lines[] = '';
        $lines[] = '*Subtotal:* '.Money::base($order->subtotal);

        if ((float) $order->discount_total > 0) {
            $lines[] = '*Descuento:* -'.Money::base($order->discount_total);
        }

        if ((float) $order->shipping_total > 0) {
            $lines[] = '*Envío:* '.Money::base($order->shipping_total);
        }

        $lines[] = '*Total:* '.Money::base($order->grand_total);

        if ($order->shippingAddress !== null) {
            $address = $order->shippingAddress;
            $lines[] = '';
            $lines[] = '*Dirección de entrega:*';
            $lines[] = "{$address->city}, {$address->department}";
            $lines[] = $address->address_line;

            if (! empty($address->references)) {
                $lines[] = "Ref: {$address->references}";
            }
        }

        if (! empty($order->notes)) {
            $lines[] = '';
            $lines[] = '*Observaciones:*';
            $lines[] = $order->notes;
        }

        $lines[] = '';
        $lines[] = '¡Gracias por tu compra en RodSant Store! ✶';

        return implode("\n", $lines);
    }

    /**
     * Enlace wa.me hacia el número de la tienda con el mensaje precargado.
     */
    public function url(Order $order): string
    {
        $number = $this->storeNumber();
        $text = rawurlencode($this->build($order));

        return "https://wa.me/{$number}?text={$text}";
    }

    /**
     * Número de WhatsApp de la tienda (solo dígitos, con indicativo país).
     */
    private function storeNumber(): string
    {
        $number = Setting::get('whatsapp_number', config('rodsant.whatsapp.number'));

        return (string) Str::of((string) $number)->replaceMatches('/\D+/', '');
    }
}
