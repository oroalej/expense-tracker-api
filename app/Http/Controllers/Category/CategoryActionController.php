<?php

namespace App\Http\Controllers\Category;

use App\DTO\Category\CategoryActionsData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryActionRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CategoryActionController extends Controller
{
    /**
     * @param  CategoryActionRequest  $request
     * @param  Category  $category
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(CategoryActionRequest $request, Category $category): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category = (new CategoryService())->actions(
                $category,
                new CategoryActionsData(
                    is_visible: $request->validated('is_visible', $category->is_visible),
                    is_budgetable: $request->validated('is_budgetable', $category->is_budgetable),
                    is_reportable: $request->validated('is_reportable', $category->is_reportable),
                )
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryResource($category),
                'message' => "$category->name category successfully created.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
