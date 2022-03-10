<?php

namespace App\Http\Controllers;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\UpdateCategory;
use App\DataObject\CategoryData;
use App\Enums\CategoryTypeState;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StoreCategoryRequest $request
	 * @return JsonResponse
	 * @throws AuthenticationException
	 */
	public function store(StoreCategoryRequest $request): JsonResponse
	{
		$categoryData = new CategoryData(
			$request->name,
			$request->description,
			CategoryTypeState::tryFrom($request->category_type),
			$request->parent_id
		);

		(new CreateCategory($categoryData))->execute();

		return response()->json([], Response::HTTP_CREATED);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param UpdateCategoryRequest $request
	 * @param Category              $category
	 * @return JsonResponse
	 */
	public function update(
		UpdateCategoryRequest $request,
		Category $category
	): JsonResponse {
		$categoryData = new CategoryData(
			$request->name,
			$request->description,
			CategoryTypeState::tryFrom($request->category_type),
			$request->parent_id
		);

		(new UpdateCategory($category, $categoryData))->execute();

		return response()->json([], Response::HTTP_OK);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Category $category
	 * @return JsonResponse
	 */
	public function destroy(Category $category): JsonResponse
	{
		$category->delete();

		return response()->json([], Response::HTTP_OK);
	}
}
