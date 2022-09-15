<?php

namespace App\Actions\CategoryGroup;

use App\Models\CategoryGroup;

class UnhideCategoryGroupAction
{
    public function execute(CategoryGroup $categoryGroup): CategoryGroup
    {
        $categoryGroup->update([
            'is_hidden' => false
        ]);

        return $categoryGroup->refresh();
    }
}
