<?php

namespace App\Actions\Category;

use App\Models\Category;

class UnhideCategoryAction
{
    public function execute(Category $category): Category
    {
        $category->update([
            'is_hidden' => false
        ]);

        return $category->refresh();
    }
}
