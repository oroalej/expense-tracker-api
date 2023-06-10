<?php

namespace App\Http\Controllers\CategoryGroup;

use App\DTO\CategoryGroupData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Destroy\DestroyCategoryGroupRequest;
use App\Http\Requests\Store\StoreCategoryGroupRequest;
use App\Http\Requests\Update\UpdateCategoryGroupRequest;
use App\Http\Resources\CategoryGroupResource;
use App\Models\CategoryGroup;
use App\Services\CategoryGroupService;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categoryGroups = CategoryGroup::select([
            'id',
            'name',
            'notes',
            'order',
            'is_hidden',
        ])
            ->with([
                'categories' => function (HasMany $builder) {
                    $builder->select(['categories.id', 'category_group_id'])
                        ->where('categories.is_hidden', false)
                        ->orderBy('categories.order');
                },
            ])
            ->where('ledger_id', $request->ledger->id)
            ->where('category_groups.is_hidden', false)
            ->orderBy('category_groups.order')
            ->get();

        return $this->apiResponse([
            'data' => CategoryGroupResource::collection($categoryGroups),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCategoryGroupRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(
        StoreCategoryGroupRequest $request
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $categoryGroup = (new CategoryGroupService())->store(
                CategoryGroupData::fromRequest($request)
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryGroupResource($categoryGroup),
                'message' => "$categoryGroup->name category group successfully created.",
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    public function show(CategoryGroup $categoryGroup): JsonResponse
    {
        return $this->apiResponse([
            'data' => new CategoryGroupResource($categoryGroup),
        ]);
    }

    /**
     * @param  UpdateCategoryGroupRequest  $request
     * @param  CategoryGroup  $categoryGroup
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        UpdateCategoryGroupRequest $request,
        CategoryGroup $categoryGroup
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $categoryGroup = (new CategoryGroupService())->update(
                $categoryGroup,
                new CategoryGroupData(
                    name: $request->name,
                    notes: $request->notes,
                    ledger: $request->ledger,
                    order: $categoryGroup->order
                )
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new CategoryGroupResource($categoryGroup),
                'message' => "$categoryGroup->name category group successfully updated",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  DestroyCategoryGroupRequest  $request
     * @param  CategoryGroup  $categoryGroup
     * @return JsonResponse
     * @throws Throwable
     */
    public function destroy(DestroyCategoryGroupRequest $request, CategoryGroup $categoryGroup): JsonResponse
    {
        DB::beginTransaction();

        try {
            $categoryGroup = (new CategoryGroupService())->delete(
                $categoryGroup,
                $request->get('category_id')
            );

            return $this->apiResponse([
                'message' => "$categoryGroup->name category group successfully deleted.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
