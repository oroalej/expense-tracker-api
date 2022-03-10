<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Wallet  $wallet
     * @return bool
     */
    public function view(User $user, Wallet $wallet): bool
    {
        return $wallet
            ->users()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Wallet  $wallet
     * @return bool
     */
    public function update(User $user, Wallet $wallet): bool
    {
        return $wallet
            ->users()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Wallet  $wallet
     * @return bool
     */
    public function delete(User $user, Wallet $wallet): bool
    {
        return $wallet
            ->users()
            ->where('user_id', $user->id)
            ->exists();
    }
}
