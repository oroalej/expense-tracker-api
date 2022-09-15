<?php

namespace App\DataTransferObjects;

use App\Enums\CategoryTypeState;

class TermData
{
    public function __construct(
        public readonly string $name,
        public readonly CategoryTypeState $categoryType
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'taxonomy_id' => $this->categoryType->value,
        ];
    }
}
