<?php

namespace App\Actions\Account;

use App\Models\Account;

class ArchiveAccountAction
{
    public function execute(Account $account): Account
    {
        $account->update([
            'is_archived' => true,
            'archived_at' => now()
        ]);

        return $account->refresh();
    }
}
