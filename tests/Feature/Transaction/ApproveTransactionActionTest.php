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

class ApproveTransactionActionTest extends TestCase
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

    public function test_approved_transaction_but_not_cleared_will_not_adjust_account_balance(): void
    {
        /** @var Transaction $transaction */
        $transaction   = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->state([
                'is_cleared'  => false,
                'is_approved' => false,
            ])
            ->create();
        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'is_cleared'  => false,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/approved")
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => true,
            'is_cleared'  => false,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_will_adjust_when_cleared_transaction_was_manually_approved()
    {
        /** @var Transaction $transaction */
        $transaction   = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow()
            ->state([
                'is_cleared'  => true,
                'is_approved' => false,
            ])
            ->create();

        $transactionId = Hashids::encode($transaction->id);

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'is_cleared'  => true,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/transactions/$transactionId/approved")
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => true,
            'is_cleared'  => true,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance + $transaction->inflow,
        ]);
    }
}
