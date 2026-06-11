<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::CategoriesView->value);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can(Permission::CategoriesView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::CategoriesCreate->value);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can(Permission::CategoriesEdit->value);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can(Permission::CategoriesDelete->value);
    }
}
