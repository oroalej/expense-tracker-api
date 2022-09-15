<?php

namespace App\Actions\AccountTypes;

use App\DataTransferObjects\AccountTypeData;
use App\Models\AccountType;

class CreateAccountType
{
    public function __construct(
        readonly AccountTypeData $accountType
    ) {
    }

    public function execute(): AccountType
    {
        $accountType = new AccountType($this->accountType->toArray());
        $accountType->accountGroupType()->associate(
            $this->accountType->accountGroupType
        );
        $accountType->save();

        return $accountType;
    }
}
