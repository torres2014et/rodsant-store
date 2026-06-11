<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Métodos de pago soportados por la tienda.
 */
enum PaymentMethod: string
{
    case Whatsapp = 'whatsapp';
    case CashOnDelivery = 'cash_on_delivery';
    case BankTransfer = 'bank_transfer';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Whatsapp => 'Cierre por WhatsApp',
            self::CashOnDelivery => 'Contraentrega',
            self::BankTransfer => 'Transferencia bancaria',
            self::Card => 'Tarjeta / pasarela en línea',
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
