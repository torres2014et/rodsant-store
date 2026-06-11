<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tipo de atributo de producto: lista simple (talla) o color (con swatch hex).
 */
enum AttributeType: string
{
    case Select = 'select';
    case Color = 'color';

    public function label(): string
    {
        return match ($this) {
            self::Select => 'Lista (ej. talla)',
            self::Color => 'Color (con muestra)',
        };
    }
}
