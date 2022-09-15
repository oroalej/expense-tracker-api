<?php

namespace App\DataTransferObjects;

use App\Models\Term;

class AccountTypeData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description,
        public readonly Term $accountGroupType,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'description' => $this->description,
        ];
    }
}
