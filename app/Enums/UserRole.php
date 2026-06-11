<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Roles del sistema. Se sincronizan con spatie/laravel-permission.
 */
enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrador',
            self::Admin => 'Administrador',
            self::Customer => 'Cliente',
        };
    }

    /**
     * Roles que tienen acceso al panel administrativo.
     *
     * @return array<int, self>
     */
    public static function staff(): array
    {
        return [self::SuperAdmin, self::Admin];
    }
}
