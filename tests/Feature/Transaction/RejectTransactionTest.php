<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class RejectTransactionTest extends TestCase
{
    public Account $account;

    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $cashAccountType = AccountType::find(AccountTypeState::Cash->value);

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for($cashAccountType)
            ->create();

        $this->category = Category::factory()
            ->for(
                CategoryGroup::factory()->for($this->ledger)
            )
            ->for($this->ledger)
            ->create();
    }

    public function test_inflow_budget_category_changes_will_be_reverted_when_transaction_was_rejected()
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->state([
                'is_approved' => false,
            ])
            ->create();

        $transactionId  = Hashids::encode($transaction->id);
        $budgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/rejected")
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'       => $budgetCategory->id,
            'activity' => $budgetCategory->activity - $transaction->inflow
        ]);
    }

    public function test_outflow_budget_category_changes_will_be_reverted_when_transaction_was_rejected()
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setOutflow()
            ->state([
                'is_approved' => false,
            ])
            ->create();

        $transactionId  = Hashids::encode($transaction->id);
        $budgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/rejected")
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'       => $budgetCategory->id,
            'activity' => $budgetCategory->activity + $transaction->outflow
        ]);
    }

    public function test_outflow_inflow_budget_category_changes_will_be_reverted_when_transaction_was_rejected()
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->state([
                'is_approved' => false,
            ])
            ->create();

        $transactionId  = Hashids::encode($transaction->id);
        $budgetCategory = BudgetCategory::getByTransaction($transaction);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/rejected")
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'id'       => $budgetCategory->id,
            'activity' => $budgetCategory->activity + $transaction->outflow - $transaction->inflow
        ]);
    }

    public function test_rejected_transaction_will_be_deleted(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->state([
                'is_approved' => false,
            ])
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/rejected")
            ->assertOk();

        $this->assertSoftDeleted($transaction);
    }
}
