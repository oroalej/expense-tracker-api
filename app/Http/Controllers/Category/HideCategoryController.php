<?php

namespace App\Http\Controllers\Category;

use App\Actions\Category\HideCategoryAction;
use App\Http\Controllers\Controller;
use App\Models\Category;

class HideCategoryController extends Controller
{
    public function __invoke(Category $category, HideCategoryAction $hideCategory)
    {
        $hideCategory->execute($category);
    }
}
