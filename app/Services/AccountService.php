<?php

namespace App\Services;

use App\DTO\AccountData;
use App\Models\Account;
use Carbon\Carbon;

class AccountService
{
    /**
     * @param  AccountData  $attributes
     * @return Account
     */
    public function store(AccountData $attributes): Account
    {
        $account = new Account([
            'name'            => $attributes->name,
            'current_balance' => $attributes->current_balance,
            'is_archived'     => $attributes->is_archived,
        ]);

        $account->ledger()->associate($attributes->ledger);
        $account->accountType()->associate($attributes->account_type);

        $account->save();

        return $account;
    }

    /**
     * @param  Account  $account
     * @param  AccountData  $attributes
     * @return Account
     */
    public function update(Account $account, AccountData $attributes): Account
    {
        $account->fill([
            'name'            => $attributes->name,
            'current_balance' => $attributes->current_balance,
        ]);

        $account->accountType()->associate($attributes->account_type);
        $account->save();

        return $account;
    }

    /**
     * @param  Account  $account
     * @param  int  $amount
     */
    public function addAccountBalance(Account $account, int $amount): void
    {
        $account->increment('current_balance', $amount);
    }

    /**
     * @param  Account  $account
     * @param  int  $amount
     */
    public function deductAccountBalance(Account $account, int $amount): void
    {
        $account->decrement('current_balance', $amount);
    }

    /**
     * @param  Account  $account
     * @return Account
     */
    public function archive(Account $account): Account
    {
        $account->fill([
            'is_archived' => true,
            'archived_at' => Carbon::now()
        ]);
        $account->save();

        return $account;
    }

    /**
     * @param  Account  $account
     * @return Account
     */
    public function unachive(Account $account): Account
    {
        $account->fill([
            'is_archived' => false,
            'archived_at' => null
        ]);
        $account->save();

        return $account;
    }
}
