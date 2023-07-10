<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VisibilityCategoryController extends Controller
{
    /**
     * @param  Category  $category
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        DB::beginTransaction();

        try {
            $category = (new CategoryService())->visible($category);

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryResource($category),
                'message' => "$category->name category is now visible",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  Category  $category
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        DB::beginTransaction();

        try {
            $category = (new CategoryService())->visible($category, false);

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryResource($category),
                'message' => "$category->name category is now hidden.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
