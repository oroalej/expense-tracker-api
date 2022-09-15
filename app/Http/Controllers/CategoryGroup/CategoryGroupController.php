<?php

namespace App\Http\Controllers\CategoryGroup;

use App\Actions\CategoryGroup\CreateCategoryGroupAction;
use App\Actions\CategoryGroup\UpdateCategoryGroupAction;
use App\DataTransferObjects\CategoryGroupData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryGroupRequest;
use App\Http\Requests\UpdateCategoryGroupRequest;
use App\Http\Resources\CategoryGroupResource;
use App\Models\CategoryGroup;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryGroupController extends Controller
{
    public function index(): JsonResponse
    {
        $categoryGroups = CategoryGroup::all();

        return response()->json(
            CategoryGroupResource::collection($categoryGroups)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCategoryGroupRequest  $request
     * @param  CreateCategoryGroupAction  $createCategoryGroup
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(
        StoreCategoryGroupRequest $request,
        CreateCategoryGroupAction $createCategoryGroup
    ): JsonResponse {
        $categoryGroup = $createCategoryGroup->execute(
            new CategoryGroupData(
                name: $request->name,
                notes: $request->notes,
                ledger: $request->ledger
            )
        );

        return response()->json(
            new CategoryGroupResource($categoryGroup),
            Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCategoryGroupRequest  $request
     * @param  CategoryGroup  $categoryGroup
     * @param  UpdateCategoryGroupAction  $updateCategoryGroup
     * @return JsonResponse
     */
    public function update(
        UpdateCategoryGroupRequest $request,
        CategoryGroup $categoryGroup,
        UpdateCategoryGroupAction $updateCategoryGroup
    ): JsonResponse {
        $categoryGroup = $updateCategoryGroup->execute(
            $categoryGroup,
            new CategoryGroupData(
                name: $request->name,
                notes: $request->notes,
                ledger: $request->ledger
            )
        );

        return response()->json(new CategoryGroupResource($categoryGroup));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  CategoryGroup  $categoryGroup
     * @return JsonResponse
     */
    public function destroy(CategoryGroup $categoryGroup)
    {
        //
    }
}
