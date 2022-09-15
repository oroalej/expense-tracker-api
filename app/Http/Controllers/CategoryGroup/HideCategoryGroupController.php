<?php

namespace App\Http\Controllers\CategoryGroup;

use App\Actions\CategoryGroup\HideCategoryGroupAction;
use App\Http\Controllers\Controller;
use App\Models\CategoryGroup;

class HideCategoryGroupController extends Controller
{
    public function __invoke(CategoryGroup $categoryGroup, HideCategoryGroupAction $hideCategoryGroup)
    {
        $hideCategoryGroup->execute($categoryGroup);
    }
}
