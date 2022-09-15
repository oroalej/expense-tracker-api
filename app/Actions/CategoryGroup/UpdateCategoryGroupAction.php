<?php

namespace App\Actions\CategoryGroup;

use App\DataTransferObjects\CategoryGroupData;
use App\Models\CategoryGroup;

class UpdateCategoryGroupAction
{
    public function execute(
        CategoryGroup $categoryGroup,
        CategoryGroupData $attributes
    ): CategoryGroup {
        $categoryGroup->fill($attributes->toArray());
        $categoryGroup->save();

        return $categoryGroup->refresh();
    }
}
