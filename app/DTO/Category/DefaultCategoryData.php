<?php

namespace App\DTO\Category;

use App\Enums\CategoryTypeState;
use App\Models\Category;

class DefaultCategoryData
{
    public function __construct(
        public readonly string $name,
        public readonly CategoryTypeState $category_type,
        public readonly ?Category $parent = null,
        public readonly ?bool $is_visible = true,
        public readonly ?bool $is_budgetable = true,
        public readonly ?bool $is_reportable = true,
        public readonly ?bool $is_editable = false,
        public readonly ?int $order = null,
        public readonly ?int $id = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'notes'         => '',
            'order'         => $this->order,
            'category_type' => $this->category_type->value,
            'is_visible'    => $this->is_visible,
            'is_budgetable' => $this->is_budgetable,
            'is_reportable' => $this->is_reportable,
            'is_editable'   => $this->is_editable
        ];
    }
}
