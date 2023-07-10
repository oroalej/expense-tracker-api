<?php

namespace Tests\Feature\Transaction;

use App\DTO\BudgetCategoryData;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\BudgetCategoryService;
use Carbon\Carbon;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreBudgetTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        $this->url = 'api/transactions';
    }

    public function test_create_current_month_year_budget_if_not_existing(): void
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $transactionDate = Carbon::now()->addYear();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($incomeCategory->id),
            'remarks'          => $this->faker->word,
            'amount'           => $this->faker->numberBetween(0, 999999),
            'transaction_date' => $transactionDate->format('Y-m-d'),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budgets', [
            'month'     => $transactionDate->get('month'),
            'year'      => $transactionDate->get('year'),
            'ledger_id' => $this->ledger->id,
        ]);
    }

    public function test_create_budget_category_if_not_existing(): void
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $transactionDate = Carbon::now()->addYear();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($incomeCategory->id),
            'remarks'          => $this->faker->word,
            'amount'           => 143,
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
            'category_id' => $incomeCategory->id,
            'budget_id'   => $budget->id,
        ]);
    }

    public function test_budget_category_is_correct_in_multiple_transactions(): void
    {
        /** @var Category $expenseCategory */
        $expenseCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $transactionDate = now();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($expenseCategory->id),
            'remarks'          => $this->faker->word,
            'amount'           => 1500,
            'transaction_date' => $transactionDate->format('Y-m-d'),
        ];

        $budgetCategory = (new BudgetCategoryService())->store(
            new BudgetCategoryData(
                category: $expenseCategory,
                budget: Budget::getDataByDateAndLedger(
                    date: $transactionDate,
                    ledger: $this->ledger
                ),
                assigned: 5000,
                available: 5000,
                activity: 5000
            )
        );

        Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($expenseCategory)
            ->setAmount(2000)
            ->state([
                'transaction_date' => $transactionDate->format('Y-m-d')
            ])
            ->cleared()
            ->count(2)
            ->create();

        $this->assertDatabaseHas('budget_categories', [
            'id'          => $budgetCategory->id,
            'category_id' => $expenseCategory->id,
            'available'   => 1000,
            'activity'    => 1000,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budget_categories', [
            'id'          => $budgetCategory->id,
            'category_id' => $expenseCategory->id,
            'assigned'    => 5000,
            'available'   => -500,
            'activity'    => -500,
        ]);
    }
}
