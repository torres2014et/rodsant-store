<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;

class AddressPolicy
{
    /**
     * El cliente solo gestiona direcciones asociadas a su propio perfil.
     */
    public function view(User $user, Address $address): bool
    {
        return $this->owns($user, $address);
    }

    public function update(User $user, Address $address): bool
    {
        return $this->owns($user, $address);
    }

    public function delete(User $user, Address $address): bool
    {
        return $this->owns($user, $address);
    }

    private function owns(User $user, Address $address): bool
    {
        return $address->customer?->user_id === $user->id;
    }
}
