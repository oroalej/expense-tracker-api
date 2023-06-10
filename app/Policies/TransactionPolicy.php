<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    protected object $ledger;

    public function __construct()
    {
        $this->ledger = (object) request()->request->get('ledger', [
            'id' => null,
            'user_id' => null
        ]);
    }

    public function store(User $user): bool
    {
        return $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Transaction  $transaction
     * @return bool
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $transaction->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Transaction  $transaction
     * @return bool
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $transaction->ledger_id === $this->ledger->id &&
           $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Transaction  $transaction
     * @return bool
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $transaction->ledger_id === $this->ledger->id &&
           $user->id === $this->ledger->user_id;
    }
}
