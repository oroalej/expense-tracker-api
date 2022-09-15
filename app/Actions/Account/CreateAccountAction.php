<?php

namespace App\Actions\Account;

use App\DataTransferObjects\AccountData;
use App\Models\Account;
use DB;
use Throwable;

class CreateAccountAction
{
    /**
     * @throws Throwable
     */
    public function execute(AccountData $attributes): Account
    {
        return DB::transaction(static function () use ($attributes) {
            $account = new Account($attributes->toArray());

            $account->ledger()->associate($attributes->ledger);
            $account->accountType()->associate($attributes->account_type);
            $account->save();

            return $account;
        });
    }
}
