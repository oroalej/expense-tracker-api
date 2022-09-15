<?php

namespace App\Http\Controllers\CategoryGroup;

use App\Actions\CategoryGroup\UnhideCategoryGroupAction;
use App\Http\Controllers\Controller;
use App\Models\CategoryGroup;

class UnhideCategoryGroupController extends Controller
{
    public function __invoke(CategoryGroup $categoryGroup, UnhideCategoryGroupAction $unhideCategoryGroup)
    {
        $unhideCategoryGroup->execute($categoryGroup);
    }
}
