<?php

namespace App\Http\Controllers\CategoryGroup;

use App\Http\Controllers\Controller;
use App\Models\CategoryGroup;
use App\Services\CategoryGroupService;
use Illuminate\Auth\Access\AuthorizationException;

class HideCategoryGroupController extends Controller
{
    /**
     * @param  CategoryGroup  $categoryGroup
     * @return void
     * @throws AuthorizationException
     */
    public function store(CategoryGroup $categoryGroup): void
    {
        $this->authorize('update', $categoryGroup);

        (new CategoryGroupService())->hide($categoryGroup);
    }

    /**
     * @param  CategoryGroup  $categoryGroup
     * @return void
     * @throws AuthorizationException
     */
    public function destroy(CategoryGroup $categoryGroup): void
    {
        $this->authorize('update', $categoryGroup);

        (new CategoryGroupService())->unhide($categoryGroup);
    }
}
