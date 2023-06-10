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
     * @return Account
     */
    public function addAccountBalance(Account $account, int $amount): Account
    {
        $account->increment('current_balance', $amount);

        return $account;
    }

    /**
     * @param  Account  $account
     * @param  int  $amount
     * @return Account
     */
    public function deductAccountBalance(Account $account, int $amount): Account
    {
        $account->decrement('current_balance', $amount);

        return $account;
    }

    /**
     * @param  Account  $account
     * @param  int  $inflow
     * @param  int  $outflow
     * @return Account
     */
    public function adjustAccountBalance(Account $account, int $inflow = 0, int $outflow = 0): Account
    {
        $adjustmentAmount = $inflow - $outflow;

        if ($adjustmentAmount) {
            $this->addAccountBalance($account, $adjustmentAmount);
        } else {
            $this->deductAccountBalance($account, $adjustmentAmount);
        }

        return $account;
    }

    /**
     * @param  Account  $account
     * @param  int  $inflow
     * @param  int  $outflow
     * @return Account
     */
    public function rollbackAccountBalance(Account $account, int $inflow = 0, int $outflow = 0): Account
    {
        $adjustmentAmount = $outflow - $inflow;

        if ($adjustmentAmount) {
            $this->addAccountBalance($account, $adjustmentAmount);
        } else {
            $this->deductAccountBalance($account, $adjustmentAmount);
        }

        return $account;
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
