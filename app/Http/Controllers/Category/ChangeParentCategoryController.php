<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ChangeParentCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChangeParentCategoryController extends Controller
{
    /**
     * @param  Category  $category
     * @param  ChangeParentCategoryRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(
        ChangeParentCategoryRequest $request,
        Category $category
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $category = (new CategoryService())->changeParentCategory(
                $category,
                Category::find($request->validated('category_id'))
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryResource($category),
                'message' => "$category->name category successfully updated parent category.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
