<?php

namespace Tests\Feature\Transaction\Income;

use App\Models\Account;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateIncomeTransactionTest extends TestCase
{
    protected Account     $account;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Account $account */
        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(0)
            ->create();

        $this->transaction = Transaction::factory()
            ->for(
                Category::factory()
                    ->for($this->ledger)
                    ->incomeType()
            )
            ->for($this->ledger)
            ->for($this->account)
            ->setAmount(2000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($this->transaction->id);
        $this->url     = "api/transactions/$transactionId";
    }

    public function test_assert_account_current_balance_is_updated_when_amount_changed(): void
    {
        $attributes = [
            'amount'           => 5000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 5000
        ]);
    }

    public function test_assert_old_account_is_reverted_when_account_is_changed(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(50)
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($anotherAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => 2050
        ]);
    }

    public function test_assert_old_account_is_reverted_when_account_and_amount_are_changed(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(50)
            ->create();

        $attributes = [
            'amount'           => 4000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($anotherAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => 4050
        ]);
    }

    public function test_assert_account_balance_is_reverted_when_category_was_change_to_different_type(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($category->id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 2000
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->transaction->refresh();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -2000
        ]);

        $this->assertDatabaseHas('budget_categories', [
            'id'        => (BudgetCategory::getByTransaction($this->transaction))->id,
            'activity'  => -2000,
            'available' => -2000,
        ]);
    }
}
