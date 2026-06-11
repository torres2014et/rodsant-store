<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estado de disponibilidad de stock, derivado de la cantidad disponible.
 */
enum StockStatus: string
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'Disponible',
            self::LowStock => 'Últimas unidades',
            self::OutOfStock => 'Agotado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InStock => 'success',
            self::LowStock => 'warning',
            self::OutOfStock => 'danger',
        };
    }

    /**
     * Resuelve el estado a partir de la cantidad disponible y el umbral de bajo stock.
     */
    public static function fromQuantity(int $available, int $lowStockThreshold = 3): self
    {
        return match (true) {
            $available <= 0 => self::OutOfStock,
            $available <= $lowStockThreshold => self::LowStock,
            default => self::InStock,
        };
    }
}
