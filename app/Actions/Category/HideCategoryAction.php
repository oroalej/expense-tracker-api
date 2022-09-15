<?php

namespace App\Actions\Category;

use App\Models\Category;

class HideCategoryAction
{
    public function execute(Category $category): Category
    {
        $category->update([
            'is_hidden' => true
        ]);

        return $category->refresh();
    }
}
