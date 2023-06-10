<?php

namespace Budget;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ShowAutoAssignTest extends TestCase
{
    public Collection $categories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Category::limit(2)
            ->get();

        $budgetCategoryData = [
            ['assigned' => 500, 'activity' => 300],
            ['assigned' => 1000, 'activity' => -600],
            ['assigned' => 1500, 'activity' => -900],
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

        $budgetId   = Hashids::encode($budgets->get(2)->getAttribute('id'));
        $categoryId = Hashids::encode($this->categories->get(1)->getAttribute('id'));
        $this->url  = "api/budgets/$budgetId/auto-assign/$categoryId";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->getJson($this->url)
            ->assertUnauthorized();
    }

    public function test_returned_data_is_correct()
    {
        $result = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->getOriginalContent()['result'];

        $this->assertEquals([
            'id'                  => Hashids::encode($this->categories->get(1)->getAttribute('id')),
            'assigned_last_month' => 2000,
            'spent_last_month'    => 1200,
            'average_assigned'    => 1500,
            'average_spent'       => 300,
        ], (array) $result);
    }

    public function test_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure([
                    'assigned_last_month',
                    'spent_last_month',
                    'average_assigned',
                    'average_spent'
                ])
            );
    }
}
