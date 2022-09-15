<?php

namespace App\Actions\CategoryGroup;

use App\DataTransferObjects\CategoryGroupData;
use App\Models\CategoryGroup;
use DB;
use Throwable;

class CreateCategoryGroupAction
{
    /**
     * @throws Throwable
     */
    public function execute(CategoryGroupData $attributes): CategoryGroup
    {
        return DB::transaction(static function () use ($attributes) {
            $categoryGroup = new CategoryGroup($attributes->toArray());

            $categoryGroup->ledger()->associate($attributes->ledger);
            $categoryGroup->save();

            return $categoryGroup;
        });
    }
}
