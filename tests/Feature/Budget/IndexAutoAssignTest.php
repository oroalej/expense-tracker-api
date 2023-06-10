<?php

namespace Tests\Feature\Budget;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class IndexAutoAssignTest extends TestCase
{
    public Collection $categories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Category::limit(2)
            ->get();

        $budgetCategoryData = [
            ['assigned' => 100, 'activity' => 1000],
            ['assigned' => 200, 'activity' => -500],
            ['assigned' => 300, 'activity' => -1500],
        ];

        $budgets = Budget::factory()
            ->for($this->ledger)
            ->state(new Sequence(
                ['month' => 1, 'year' => 2023, 'date' => "2023-01-01"],
                ['month' => 2, 'year' => 2023, 'date' => "2023-02-01"],
                ['month' => 3, 'year' => 2023, 'date' => "2023-03-01"],
            ))
            ->count(3)
            ->create();

        $this->categories->each(function (Category $category, int $categoryIndex) use ($budgets, $budgetCategoryData) {
            $budgets->each(function (Budget $budget, int $budgetIndex) use (
                $category,
                $categoryIndex,
                $budgetCategoryData
            ) {
                BudgetCategory::factory()
                    ->for($budget)
                    ->for($category)
                    ->create([
                        'assigned' => $budgetCategoryData[$budgetIndex]['assigned'] * ($categoryIndex + 1),
                        'activity' => $budgetCategoryData[$budgetIndex]['activity'] * ($categoryIndex + 1)
                    ]);
            });
        });

        $budgetId = Hashids::encode($budgets->get(2)->getAttribute('id'));

        $this->url = "api/budgets/$budgetId/auto-assign";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->getJson($this->url)
            ->assertUnauthorized();
    }

    public function test_returned_data_is_correct()
    {
        $response = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->getContent();

        $result = collect((json_decode($response))->result);
        $data   = [
            [
                'id'                  => Hashids::encode($this->categories->get(0)?->id),
                'average_assigned'    => 150,
                'average_spent'       => 250,
                'assigned_last_month' => 200,
                'spent_last_month'    => 500,
            ], [
                'id'                  => Hashids::encode($this->categories->get(1)?->id),
                'average_assigned'    => 300,
                'average_spent'       => 500,
                'assigned_last_month' => 400,
                'spent_last_month'    => 1000,
            ]
        ];

        foreach ($data as $expected) {
            $budgetCategory = $result->firstWhere('id', $expected['id']);

            $this->assertEquals(
                $expected,
                [
                    'id'                  => $budgetCategory->id,
                    'average_assigned'    => $budgetCategory->average_assigned,
                    'average_spent'       => $budgetCategory->average_spent,
                    'assigned_last_month' => $budgetCategory->assigned_last_month,
                    'spent_last_month'    => $budgetCategory->spent_last_month,
                ]
            );
        }
    }

    public function test_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertJsonStructure(
                $this->apiStructure(
                    $this->apiStructureCollection([
                        'id',
                        'assigned_last_month',
                        'spent_last_month',
                        'average_assigned',
                        'average_spent'
                    ])
                )
            );
    }
}
