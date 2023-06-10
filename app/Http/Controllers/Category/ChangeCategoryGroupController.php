<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryGroupUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChangeCategoryGroupController extends Controller
{
    /**
     * @param  Category  $category
     * @param  CategoryGroupUpdateRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(
        Category $category,
        CategoryGroupUpdateRequest $request
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $category = (new CategoryService())->changeCategoryGroup(
                $category,
                CategoryGroup::find($request->get('category_group_id'))
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryResource($category),
                'message' => "$category->name category successfully updated.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
