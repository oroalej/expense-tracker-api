<?php

namespace App\DataObject;

use App\Enums\CategoryTypeState;

class CategoryData
{
	public function __construct(
		public string $name,
		public string $description,
		public CategoryTypeState $category_type,
		public int|null $parent_id = null,
		public bool $is_default = false,
		public bool $is_editable = true
	) {
	}
}
