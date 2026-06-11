<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Error de dominio del carrito (stock insuficiente, producto no disponible,
 * carrito vacío…). Lleva un mensaje apto para mostrar al cliente.
 */
class CartException extends RuntimeException
{
    public static function unavailable(string $product): self
    {
        return new self("«{$product}» ya no está disponible.");
    }

    public static function outOfStock(string $product): self
    {
        return new self("«{$product}» está agotado por el momento.");
    }

    public static function insufficientStock(string $product, int $available): self
    {
        $units = $available === 1 ? '1 unidad' : "{$available} unidades";

        return new self("Solo quedan {$units} de «{$product}».");
    }

    public static function emptyCart(): self
    {
        return new self('Tu carrito está vacío.');
    }
}
