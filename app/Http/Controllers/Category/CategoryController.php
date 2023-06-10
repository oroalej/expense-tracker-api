<?php

namespace App\Http\Controllers\Category;

use App\DTO\CategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Destroy\DestroyCategoryRequest;
use App\Http\Requests\Store\StoreCategoryRequest;
use App\Http\Requests\Update\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::select([
            'id', 'name', 'notes', 'order', 'category_group_id'
        ])
            ->where('ledger_id', $request->ledger->id)
            ->withCount('transactions')
            ->orderBy('category_group_id')
            ->orderBy('order')
            ->get();

        return $this->apiResponse([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->apiResponse([
            'data' => new CategoryResource($category),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CategoryGroup  $categoryGroup
     * @param  StoreCategoryRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(
        CategoryGroup $categoryGroup,
        StoreCategoryRequest $request
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $category = (new CategoryService())->store(
                new CategoryData(
                    name: $request->validated('name'),
                    category_group: $categoryGroup,
                    ledger: $request->ledger,
                    notes: $request->validated('notes'),
                )
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryResource($category),
                'message' => "$category->name category successfully created.",
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  UpdateCategoryRequest  $request
     * @param  Category  $category
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $category = (new CategoryService())->update(
                $category,
                new CategoryData(
                    name: $request->validated('name'),
                    category_group: $category->categoryGroup,
                    ledger: $request->ledger,
                    notes: $request->validated('notes'),
                    order: $category->order
                )
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category  $category
     * @param  DestroyCategoryRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function destroy(DestroyCategoryRequest $request, Category $category): JsonResponse
    {
        DB::beginTransaction();

        try {
            (new CategoryService())->delete(
                $category,
                $request->get('category_id')
            );

            DB::commit();

            return $this->apiResponse([
                'message' => "$category->name category successfully deleted.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
