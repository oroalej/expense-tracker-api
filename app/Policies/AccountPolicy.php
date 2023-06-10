<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
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

    /**
     * @param  User  $user
     * @return bool
     */
    public function store(User $user): bool
    {
        return $user->id === $this->ledger->user_id;
    }


    /**
     * @param  User  $user
     * @param  Account  $account
     * @return bool
     */
    public function view(User $user, Account $account): bool
    {
        return $account->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Account  $account
     * @return bool
     */
    public function update(User $user, Account $account): bool
    {
        return $account->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }

    /**
     * @param  User  $user
     * @param  Account  $account
     * @return bool
     */
    public function delete(User $user, Account $account): bool
    {
        return $account->ledger_id === $this->ledger->id &&
            $user->id === $this->ledger->user_id;
    }
}
