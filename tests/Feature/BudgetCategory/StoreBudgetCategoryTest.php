<?php

namespace Tests\Feature\BudgetCategory;

use App\Models\Budget;
use App\Models\Category;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreBudgetCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var Budget $budget */
        $budget = Budget::factory()
            ->for($this->ledger)
            ->create();

        $budgetId = Hashids::encode($budget->id);

        $this->url = "api/budgets/$budgetId/budget-categories";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->postJson($this->url)
            ->assertUnauthorized();
    }

    public function test_category_id_is_required()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_valid()
    {
        $this
            ->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => 999999
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_user_can_create_budget_category(): void
    {
        /** @var Category $category */
        $category = $this->user
            ->categories()
            ->inRandomOrder()
            ->first();

        $attributes = [
            'category_id' => Hashids::encode($category->id),
            'amount'      => $this->faker->numberBetween(0, 99999)
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('budget_categories', 1);
        $this->assertDatabaseHas('budget_categories', [
            'assigned'    => $attributes['amount'],
            'category_id' => $category->id
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        /** @var Category $category */
        $category = $this->user
            ->categories()
            ->inRandomOrder()
            ->first();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode($category->id),
                'amount'      => $this->faker->numberBetween(0, 99999),
            ])
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'category_id',
                    'budget_id',
                    'assigned',
                    'activity',
                    'available',
                ])
            );
    }
}
