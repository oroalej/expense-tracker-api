<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  User  $user
     * @param  Transaction  $transaction
     * @return bool
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  User  $user
     * @param  Transaction  $transaction
     * @return bool
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  User  $user
     * @param  Transaction  $transaction
     * @return bool
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
