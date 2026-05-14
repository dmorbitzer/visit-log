<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    public function create(User $authUser): bool
    {
        return $authUser->isAdmin();
    }

    public function update(User $authUser, User $user): bool
    {
        return $authUser->isAdmin();
    }

    public function delete(User $authUser, User $user): bool
    {
        return $authUser->isAdmin() && $authUser->id !== $user->id;
    }
}
