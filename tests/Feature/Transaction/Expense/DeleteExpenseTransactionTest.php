<?php

namespace Tests\Feature\Transaction\Expense;

use App\Models\Account;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class DeleteExpenseTransactionTest extends TestCase
{
    public Transaction $transaction;
    public Account     $account;

    protected function setUp(): void
    {
        parent::setUp();

        $expenseCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        $this->transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($expenseCategory)
            ->setAmount(50000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($this->transaction->id);

        $this->url = "api/transactions/$transactionId";
    }

    public function test_assert_budget_category_reverted()
    {
        $budgetCategory = BudgetCategory::getByTransaction($this->transaction);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'activity'  => -50000,
            'available' => -50000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $budgetCategory->refresh();

        $this->assertDatabaseHas('budget_categories', [
            'id'        => $budgetCategory->id,
            'activity'  => 0,
            'available' => 0
        ]);
    }

    public function test_assert_account_balance_was_reverted_when_expense_transaction_is_approved_and_cleared()
    {
        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -50000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);
    }
}
