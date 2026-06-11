<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::OrdersView->value);
    }

    /**
     * El staff con permiso ve cualquier pedido; el cliente solo los suyos.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->can(Permission::OrdersView->value)) {
            return true;
        }

        return $order->customer?->user_id === $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can(Permission::OrdersEdit->value);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->can(Permission::OrdersEdit->value);
    }

    /**
     * Exportar el listado de pedidos (CSV/Excel desde el panel).
     */
    public function export(User $user): bool
    {
        return $user->can(Permission::OrdersExport->value);
    }
}
