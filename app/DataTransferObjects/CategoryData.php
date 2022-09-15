<?php

namespace App\DataTransferObjects;

use App\Models\CategoryGroup;

class CategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $notes,
        public readonly CategoryGroup $categoryGroup
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'notes' => $this->notes
        ];
    }
}
