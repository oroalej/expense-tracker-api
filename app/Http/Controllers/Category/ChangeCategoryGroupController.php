<?php

namespace App\Http\Controllers\Category;

use App\Actions\Category\ChangeCategoryGroupAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryGroupUpdateRequest;
use App\Models\Category;
use App\Models\CategoryGroup;

class ChangeCategoryGroupController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  Category  $category
     * @param  CategoryGroupUpdateRequest  $request
     * @param  ChangeCategoryGroupAction  $changeCategoryGroup
     */
    public function __invoke(
        Category $category,
        CategoryGroupUpdateRequest $request,
        ChangeCategoryGroupAction $changeCategoryGroup
    ) {
        $changeCategoryGroup->execute(
            $category,
            CategoryGroup::findUuid($request->id)
        );
    }
}
