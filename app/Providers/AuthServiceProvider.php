<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /*
         * Bypass global para el Super Admin: cualquier verificación de
         * autorización (permisos, policies o gates) se concede automáticamente.
         *
         * Devolver null deja que el resto de comprobaciones sigan su curso normal
         * para los demás roles. Es importante NO devolver false aquí para no
         * cortar otras reglas de autorización.
         */
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole(UserRole::SuperAdmin->value) ? true : null;
        });

        /*
         * Gate de acceso al panel administrativo. Lo consumirá Filament
         * (canAccessPanel) y cualquier middleware de área privada.
         */
        Gate::define('access-admin', static fn (User $user): bool => $user->isStaff());
    }
}
