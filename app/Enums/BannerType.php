<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Ubicaciones/tipos de banner gestionables desde el panel.
 */
enum BannerType: string
{
    case Announcement = 'announcement';
    case Hero = 'hero';
    case Collection = 'collection';
    case Category = 'category';
    case Popup = 'popup';
    case Strip = 'strip';

    public function label(): string
    {
        return match ($this) {
            self::Announcement => 'Barra de anuncio',
            self::Hero => 'Banner principal (Hero)',
            self::Collection => 'Bloque de colección',
            self::Category => 'Cabecera de categoría',
            self::Popup => 'Ventana emergente',
            self::Strip => 'Banda intermedia',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            static fn (array $carry, self $case): array => $carry + [$case->value => $case->label()],
            [],
        );
    }
}
