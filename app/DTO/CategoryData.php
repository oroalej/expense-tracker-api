<?php

namespace App\DTO;

use App\Models\CategoryGroup;
use App\Models\Ledger;

class CategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly CategoryGroup $category_group,
        public readonly Ledger $ledger,
        public readonly ?string $notes = "",
        public readonly ?int $order = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name'  => $this->name,
            'notes' => $this->notes,
            'order' => $this->order,
        ];
    }
}
