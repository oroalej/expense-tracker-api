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

class ClearTransactionActionTest extends TestCase
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

    public function test_cleared_transaction_will_automatically_be_approved()
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->state([
                'is_cleared' => false,
                'is_approved' => false
            ])
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'approved_at' => null,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/cleared")
            ->assertOk();

        $transaction->refresh();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => true,
            'is_cleared'  => true
        ]);
        $this->assertNotNull($transaction->cleared_at);
        $this->assertNotNull($transaction->approved_at);
    }

    public function test_account_balance_will_adjust_once_transaction_is_cleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->state([
                'is_cleared' => false,
            ])
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id'         => $transaction->id,
            'is_cleared' => false,
            'cleared_at' => null,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/cleared")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance + $transaction->inflow,
        ]);
    }
}
