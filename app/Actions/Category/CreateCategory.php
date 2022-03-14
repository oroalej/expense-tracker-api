<?php

namespace App\Actions\Category;

use App\DataObject\CategoryData;
use App\Models\Category;
use App\Models\User;

class CreateCategory
{
    public function __construct(
        protected CategoryData $attributes,
        protected User $user
    ) {
    }

    public function execute(): Category
    {
        $category = new Category([
            'name' => $this->attributes->name,
            'description' => $this->attributes->description,
            'category_type' => $this->attributes->category_type->value,
        ]);

        $category->user()->associate($this->user);

        if ($this->attributes->parent_id) {
            $category->parent()->associate($this->attributes->parent_id);
        }

        $category->save();

        return $category;
    }
}
