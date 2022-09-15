<?php

namespace App\Actions\Account;

use App\Models\Account;

class AddCurrentBalanceAction
{
    public function execute(Account $account, float $amount = 0): void
    {
        $account->update([
            'current_balance' => $account->current_balance + $amount,
        ]);
    }
}
