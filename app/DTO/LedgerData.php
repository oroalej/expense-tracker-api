<?php

namespace App\DTO;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class LedgerData
{
    public function __construct(
        public readonly string $name,
        public readonly string $date_format,
        public readonly User|Authenticatable $user,
        public readonly Currency $currency,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'date_format' => $this->date_format
        ];
    }
}
