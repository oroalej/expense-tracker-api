<?php

namespace App\Http\Controllers\Category;

use App\DTO\CategoryData;
use App\Enums\CategoryTypeState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\DestroyCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\CustomRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Vinkla\Hashids\Facades\Hashids;

class CategoryController extends Controller
{
    public function index(CustomRequest $request): JsonResponse
    {
        $categories = Category::where('ledger_id', $request->ledger->id)
            ->with('child', function (Builder $builder) {
                $builder->select(['parent_id', 'id'])
                    ->orderBy('order');
            })
            ->withCount('transactions')
            ->orderBy('order')
            ->get();

        $ids = [];

        foreach (CategoryTypeState::cases() as $value) {
            $ids[strtolower($value->name)] = $categories
                ->filter(fn (Category $category) => $category->category_type->value === $value->value && $category->parent_id === null)
                ->pluck('id')
                ->map(fn (int $categoryId) => Hashids::encode($categoryId))
                ->toArray();
        }

        return $this->apiResponse([
            'data' => [
                'ids' => $ids,
                'entities' => CategoryResource::collection($categories),
            ]
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
     * @param  StoreCategoryRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $category = (new CategoryService())->store(
                CategoryData::fromRequest($request)
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
                CategoryData::fromRequest($request)
            );

            DB::commit();

            $category->loadCount('transactions');
            $category->loadMissing([
                'child' => static function (Builder $builder) {
                    $builder->select('parent_id', 'id', 'order')
                        ->orderBy('order');
                }
            ]);

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
                $request->validated('category_id')
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
