<?php

namespace App\Actions\Category;

use App\DataTransferObjects\CategoryData;
use App\Models\Category;
use DB;
use Throwable;

class CreateCategoryAction
{
    /**
     * @throws Throwable
     */
    public function execute(CategoryData $attributes): Category
    {
        return DB::transaction(static function () use ($attributes) {
            $category = new Category($attributes->toArray());

            $category->categoryGroup()->associate($attributes->categoryGroup);
            $category->save();

            return $category;
        });
    }
}
