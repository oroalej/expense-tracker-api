<?php

namespace App\Actions\Account;

use App\DataTransferObjects\AccountData;
use App\Models\Account;

class UpdateAccountAction
{
    public function execute(Account $account, AccountData $attributes): Account
    {
        $account->update([
            'name' => $attributes->name,
            'current_balance' => $attributes->current_balance,
        ]);

        return $account->refresh();
    }
}
