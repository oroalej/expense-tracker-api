<?php

namespace App\DTO\Category;

class CategoryActionsData
{
    public function __construct(
        public readonly ?bool $is_visible = null,
        public readonly ?bool $is_budgetable = null,
        public readonly ?bool $is_reportable = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'is_visible'    => $this->is_visible,
            'is_budgetable' => $this->is_budgetable,
            'is_reportable' => $this->is_reportable,
        ];
    }
}
