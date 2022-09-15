<?php

namespace App\Actions\Account;

use App\Models\Account;

class UnarchiveAccountAction
{
    public function execute(Account $account): Account
    {
        $account->update([
            'is_archived' => false,
            'archived_at' => null
        ]);

        return $account->refresh();
    }
}
