<?php

namespace App\DataTransferObjects;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class LedgerData
{
    public function __construct(
        public readonly string $name,
        public readonly User|Authenticatable $user,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
