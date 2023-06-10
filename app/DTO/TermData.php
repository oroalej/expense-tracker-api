<?php

namespace App\DTO;

use App\Enums\CategoryTypeState;

class TermData
{
    public function __construct(
        public readonly string $name,
        public readonly CategoryTypeState $category_type
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'taxonomy_id' => $this->category_type->value,
        ];
    }
}
