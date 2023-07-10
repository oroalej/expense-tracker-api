<?php

namespace Tests\Feature\Category;

use App\Enums\CategoryTypeState;
use App\Models\Category;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class IndexCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/categories";
    }

    public function test_guest_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_parent_category_in_correct_order(): void
    {
        $categories = [];

        foreach (CategoryTypeState::cases() as $case) {
            $categories[strtolower($case->name)] = Category::select(['id', 'ledger_id', 'parent_id', 'category_type', 'order'])
                ->where('ledger_id', $this->ledger->id)
                ->where('parent_id')
                ->where('category_type', $case->value)
                ->orderBy('order')
                ->pluck('id')
                ->map(fn (int $id) => Hashids::encode($id))
                ->toArray();
        }

        $response = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->getContent();

        $ids = json_decode($response)->result->ids;

        $this->assertEquals($categories['income'], $ids->income);
        $this->assertEquals($categories['expense'], $ids->expense);
    }

    public function test_api_has_correct_structure()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure(
                    [
                        'ids',
                        'entities' => $this->apiStructureCollection([
                            'id',
                            'parent_id',
                            'name',
                            'order',
                            'category_type',
                            'is_visible',
                            'is_budgetable',
                            'is_reportable',
                            'transactions_count',
                            'child'
                        ])
                    ]
                )
            );
    }
}
