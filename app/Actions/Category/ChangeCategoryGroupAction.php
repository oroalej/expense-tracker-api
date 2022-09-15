<?php

namespace App\Actions\Category;

use App\Models\Category;
use App\Models\CategoryGroup;

class ChangeCategoryGroupAction
{
    public function execute(Category $category, CategoryGroup $categoryGroup): Category
    {
        $category->categoryGroup()->associate($categoryGroup);
        $category->save();

        return $category->refresh();
    }
}
