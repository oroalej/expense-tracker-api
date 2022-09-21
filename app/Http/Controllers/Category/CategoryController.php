<?php

namespace App\Http\Controllers\Category;

use App\Actions\Category\CreateCategoryAction;
use App\Actions\Category\UpdateCategoryAction;
use App\DataTransferObjects\CategoryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('category_group_id')->get();

        return response()->json(CategoryResource::collection($categories));
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json(new CategoryResource($category));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCategoryRequest  $request
     * @param  CreateCategoryAction  $createCategory
     * @param  CategoryGroup  $categoryGroup
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(
        StoreCategoryRequest $request,
        CreateCategoryAction $createCategory,
        CategoryGroup $categoryGroup
    ): JsonResponse {
        $order = $categoryGroup->categories()->count();
        
        $category = $createCategory->execute(
            new CategoryData(
                name: $request->name,
                notes: $request->notes,
                order: $order + 1,
                categoryGroup: $categoryGroup
            )
        );

        return response()->json(
            new CategoryResource($category),
            Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCategoryRequest  $request
     * @param  CategoryGroup  $categoryGroup
     * @param  Category  $category
     * @param  UpdateCategoryAction  $updateCategory
     * @return JsonResponse
     */
    public function update(
        UpdateCategoryRequest $request,
        CategoryGroup $categoryGroup,
        Category $category,
        UpdateCategoryAction $updateCategory
    ): JsonResponse {
        $category = $updateCategory->execute(
            $category,
            new CategoryData(
                name: $request->name,
                notes: $request->notes,
                order: $category->order,
                categoryGroup: $categoryGroup
            )
        );

        return response()->json(
            new CategoryResource($category),
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category  $category
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([], Response::HTTP_OK);
    }
}
