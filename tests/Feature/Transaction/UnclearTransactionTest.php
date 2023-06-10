<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnclearTransactionTest extends TestCase
{
    public Account $account;

    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $cashAccountType = AccountType::find(AccountTypeState::Cash->value);
        $this->account   = Account::factory()
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

    public function test_transaction_automatically_approved_when_uncleared()
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->state([
                'is_approved' => false,
                'is_cleared' => true
            ])
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/uncleared")
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_cleared'  => false,
            'is_approved' => true,
        ]);

        $transaction->refresh();

        $this->assertNotNull($transaction->approved_at);
    }

    public function test_account_balance_reverted_once_transaction_was_uncleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_cleared'  => true,
            'is_approved' => true,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/uncleared")
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_cleared'  => false,
            'is_approved' => true,
        ]);

        $transaction->refresh();

        $this->assertNull($transaction->cleared_at);
        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance - $transaction->inflow,
        ]);
    }
}
