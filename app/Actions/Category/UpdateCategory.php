<?php

namespace App\Actions\Category;

use App\DataObject\CategoryData;
use App\Models\Category;

class UpdateCategory
{
	public function __construct(
		protected Category $category,
		protected CategoryData $attributes
	) {
	}

	public function execute(): Category
	{
		$this->category->fill([
			'name' => $this->attributes->name,
			'description' => $this->attributes->description,
			'category_type' => $this->attributes->category_type->value,
		]);

		if ($this->attributes->parent_id) {
			$this->category->parent()->associate($this->attributes->parent_id);
		}

		$this->category->save();

		return $this->category->refresh();
	}
}
