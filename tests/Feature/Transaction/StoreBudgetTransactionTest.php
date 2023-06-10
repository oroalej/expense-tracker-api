<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use Carbon\Carbon;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreBudgetTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $cashAccountType = AccountType::find(AccountTypeState::Cash->value);

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for($cashAccountType)
            ->create();

        $this->url = "api/transactions";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)
            ->assertUnauthorized();
    }

    public function test_create_current_month_year_budget_if_not_existing(): void
    {
        /** @var Category $category */
        $category = $this->ledger
            ->categories()
            ->inRandomOrder()
            ->first();

        $transactionDate = Carbon::now()->addYear();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(0, 999999),
            'transaction_date' => $transactionDate->format('Y-m-d'),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budgets', [
            'month'     => $transactionDate->get('month'),
            'year'      => $transactionDate->get('year'),
            'ledger_id' => $this->ledger->id
        ]);
    }

    public function test_create_budget_category_if_not_existing(): void
    {
        /** @var Category $category */
        $category = $this->ledger
            ->categories()
            ->inRandomOrder()
            ->first();

        $transactionDate = Carbon::now()->addYear();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(0, 999999),
            'transaction_date' => $transactionDate->format('Y-m-d'),
        ];

        /** @var Budget $budget */
        $budget = Budget::factory()
            ->for($this->ledger)
            ->state([
                'month' => $transactionDate->get('month'),
                'year'  => $transactionDate->get('year'),
            ])
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budget_categories', [
            'category_id' => $category->id,
            'budget_id'   => $budget->id
        ]);
    }

    public function test_budget_category_is_updated_when_transaction_is_approved_but_not_cleared(): void
    {
        /** @var Category $category */
        $category = $this->ledger
            ->categories()
            ->inRandomOrder()
            ->first();

        $transactionDate = now();
        $attributes      = [
            'category_id'      => Hashids::encode($category->id),
            'account_id'       => Hashids::encode($this->account->id),
            'remarks'          => $this->faker->word,
            'inflow'           => 10000,
            'outflow'          => 0,
            'transaction_date' => $transactionDate->format('Y-m-d'),
            'is_cleared'       => false,
            'is_approved'      => true
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $budgetId = Budget::filterByMonthYearAndLedgerId(
            month: $transactionDate->get('month'),
            year: $transactionDate->get('year'),
            ledgerId: $this->ledger->id
        )->value('id');

        $this->assertDatabaseHas('budget_categories', [
            'category_id' => $category->id,
            'budget_id'   => $budgetId,
            'assigned'    => 0,
            'available'   => $attributes['inflow'],
            'activity'    => $attributes['inflow'],
        ]);
    }

    public function test_budget_category_is_correct_during_inflow_transaction(): void
    {
        /** @var Category $category */
        $category = $this->ledger
            ->categories()
            ->inRandomOrder()
            ->first();

        $transactionDate = now();
        $attributes      = [
            'category_id'      => Hashids::encode($category->id),
            'account_id'       => Hashids::encode($this->account->id),
            'remarks'          => $this->faker->word,
            'inflow'           => 200000,
            'outflow'          => 0,
            'transaction_date' => $transactionDate->format('Y-m-d'),
            'is_cleared'       => true,
            'is_approved'      => true
        ];

        $budget = Budget::where('month', $transactionDate->get('month'))
            ->where('year', $transactionDate->get('year'))
            ->where('ledger_id', $this->ledger->id)
            ->first();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budget_categories', [
            'category_id' => $category->id,
            'budget_id'   => $budget->id,
            'assigned'    => 0,
            'available'   => $attributes['inflow'],
            'activity'    => $attributes['inflow'],
        ]);
    }

    public function test_budget_category_is_correct_on_outflow_transaction(): void
    {
        /** @var Category $category */
        $category = $this->ledger
            ->categories()
            ->inRandomOrder()
            ->first();

        $transactionDate = now();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'outflow'          => 200000,
            'transaction_date' => $transactionDate->format('Y-m-d'),
        ];

        $budget = Budget::where('month', $transactionDate->get('month'))
            ->where('year', $transactionDate->get('year'))
            ->where('ledger_id', $this->ledger->id)
            ->first();

        /** @var BudgetCategory $budgetCategory */
        $budgetCategory = BudgetCategory::factory()
            ->for($budget)
            ->for($category)
            ->state([
                'assigned'  => 100000,
                'available' => 0,
                'activity'  => 0,
            ])
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'assigned'  => $budgetCategory->assigned,
            'available' => $budgetCategory->assigned - $attributes['outflow'],
            'activity'  => $budgetCategory->activity - $attributes['outflow'],
        ]);
    }
}
