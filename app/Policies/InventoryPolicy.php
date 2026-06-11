<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $user->can(Permission::InventoryView->value);
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->can(Permission::InventoryEdit->value);
    }
}
