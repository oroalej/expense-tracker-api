<?php

namespace App\Http\Controllers\Account;

use App\Actions\Account\UnarchiveAccountAction;
use App\Http\Controllers\Controller;
use App\Models\Account;

class UnarchiveAccountController extends Controller
{
    public function __invoke(Account $account, UnarchiveAccountAction $unarchiveAccount)
    {
        $unarchiveAccount->execute($account);
    }
}
