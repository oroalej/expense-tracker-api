<?php

namespace App\DataTransferObjects;

use App\Models\Ledger;

class CategoryGroupData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $notes,
        public readonly int $order,
        public readonly Ledger $ledger,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'notes' => $this->notes,
            'order' => $this->order,
        ];
    }
}
