<?php

namespace Tests\Feature\Transaction\Expense;

use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreExpenseTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        $this->url = "api/transactions";
    }

    public function test_user_can_create_expense_transaction(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'amount'           => 10000,
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'account_id'  => $this->account->id,
            'category_id' => $category->id,
            'remarks'     => $attributes['remarks'],
            'amount'      => $attributes['amount'],
        ]);
    }

    public function test_account_balance_was_updated_after_creating_expense_transaction(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'amount'           => 10000,
            'transaction_date' => $this->faker->date,
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -10000
        ]);
    }

    public function test_budget_category_was_updated_after_creating_expense_transaction(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $transactionDate = now();
        $attributes      = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'amount'           => 10000,
            'transaction_date' => $transactionDate->format('Y-m-d'),
        ];

        $budgetCategory = BudgetCategory::getOrCreateByBudgetAndCategory(
            budget: Budget::getDataByDateAndLedger($transactionDate, $this->ledger),
            category: $category
        );

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'available' => 0,
            'activity'  => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $this->account->id,
            'available' => -10000,
            'activity'  => -10000
        ]);
    }
}
