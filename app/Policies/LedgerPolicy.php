<?php

namespace App\Policies;

use App\Models\Ledger;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LedgerPolicy
{
    use HandlesAuthorization;

    /**
     * @param  User  $user
     * @param  Ledger  $ledger
     * @return bool
     */
    public function view(User $user, Ledger $ledger): bool
    {
        return $ledger->user_id === $user->id;
    }


    /**
     * @param  User  $user
     * @param  Ledger  $ledger
     * @return bool
     */
    public function update(User $user, Ledger $ledger): bool
    {
        return $ledger->user_id === $user->id;
    }

    /**
     * @param  User  $user
     * @param  Ledger  $ledger
     * @return bool
     */
    public function delete(User $user, Ledger $ledger): bool
    {
        return $ledger->user_id === $user->id;
    }
}
