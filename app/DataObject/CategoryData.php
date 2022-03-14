<?php

namespace App\DataObject;

use App\Enums\CategoryTypeState;

class CategoryData
{
    public function __construct(
        public string $name,
        public CategoryTypeState $category_type,
        public string|null $description = null,
        public int|null $parent_id = null,
        public bool $is_editable = true
    ) {
    }
}
