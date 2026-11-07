<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * The admin order and loyalty history page exists only for client accounts.
     */
    public function viewHistory(User $user, User $account): bool
    {
        return $user->isAdmin() && $account->isClient();
    }
}
