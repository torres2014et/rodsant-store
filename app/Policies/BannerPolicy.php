<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Banner;
use App\Models\User;

class BannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::BannersView->value);
    }

    public function view(User $user, Banner $banner): bool
    {
        return $user->can(Permission::BannersView->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::BannersCreate->value);
    }

    public function update(User $user, Banner $banner): bool
    {
        return $user->can(Permission::BannersEdit->value);
    }

    public function delete(User $user, Banner $banner): bool
    {
        return $user->can(Permission::BannersDelete->value);
    }
}
