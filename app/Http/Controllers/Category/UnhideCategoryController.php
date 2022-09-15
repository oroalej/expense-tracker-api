<?php

namespace App\Http\Controllers\Category;

use App\Actions\Category\UnhideCategoryAction;
use App\Http\Controllers\Controller;
use App\Models\Category;

class UnhideCategoryController extends Controller
{
    public function __invoke(Category $category, UnhideCategoryAction $unhideCategory)
    {
        $unhideCategory->execute($category);
    }
}
