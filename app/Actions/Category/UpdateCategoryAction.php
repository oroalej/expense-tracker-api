<?php

namespace App\Actions\Category;

use App\DataTransferObjects\CategoryData;
use App\Models\Category;

class UpdateCategoryAction
{
    public function execute(
        Category $category,
        CategoryData $attributes
    ): Category {
        $category->fill($attributes->toArray());
        $category->save();

        return $category->refresh();
    }
}
