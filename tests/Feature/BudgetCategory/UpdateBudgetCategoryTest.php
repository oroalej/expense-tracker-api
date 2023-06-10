<?php

namespace Tests\Feature\BudgetCategory;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateBudgetCategoryTest extends TestCase
{
    public BudgetCategory $budgetCategory;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Budget $budget */
        $budget = Budget::factory()
            ->for($this->ledger)
            ->create();

        $category = $this->ledger
            ->categories()
            ->inRandomOrder()
            ->first();

        $this->budgetCategory = BudgetCategory::factory()
            ->for($budget)
            ->for($category)
            ->create();

        $budgetId         = Hashids::encode($budget->id);
        $budgetCategoryId = Hashids::encode($this->budgetCategory->id);

        $this->url = "api/budgets/$budgetId/budget-categories/$budgetCategoryId";
    }

    public function test_assert_guest_not_allowed(): void
    {
        $this->putJson($this->url)
            ->assertUnauthorized();
    }

    public function test_a_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_amount_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_is_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['amount' => 'Hello Reviewer'])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accept_positive_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['amount' => -2022])
            ->assertJsonValidationErrors('amount');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['amount' => 0])
            ->assertJsonMissingValidationErrors('amount');
    }

    public function test_assert_user_can_update_assigned_amount(): void
    {
        $attributes = [
            'amount' => $this->faker->numberBetween(0, 99999)
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $this->budgetCategory->id,
            'assigned'  => $attributes['amount'],
            'available' => $attributes['amount'] - $this->budgetCategory->activity
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['amount' => $this->faker->numberBetween(0, 99999)])
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'category_id',
                    'budget_id',
                    'assigned',
                    'available',
                    'activity',
                ])
            );
    }
}
