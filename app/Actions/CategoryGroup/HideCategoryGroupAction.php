<?php

namespace App\Actions\CategoryGroup;

use App\Models\CategoryGroup;

class HideCategoryGroupAction
{
    public function execute(CategoryGroup $categoryGroup): CategoryGroup
    {
        $categoryGroup->update([
            'is_hidden' => true
        ]);

        return $categoryGroup->refresh();
    }
}
