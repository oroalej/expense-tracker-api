<?php

namespace App\Http\Controllers\Account;

use App\Actions\Account\ArchiveAccountAction;
use App\Http\Controllers\Controller;
use App\Models\Account;

class ArchiveAccountController extends Controller
{
    public function __invoke(Account $account, ArchiveAccountAction $archiveAccount)
    {
        $archiveAccount->execute($account);
    }
}
