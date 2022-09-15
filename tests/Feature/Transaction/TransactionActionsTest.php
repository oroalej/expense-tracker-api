<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Tests\TestCase;

class TransactionActionsTest extends TestCase
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
            ->create();
    }

    public function test_transaction_will_automatically_approved_when_user_updated_cleared_field(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->state([
                'is_approved' => false,
                'is_cleared' => false
            ])
            ->create();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'is_cleared'  => false,
            'approved_at' => null,
            'cleared_at'  => null
        ]);

        $this->actingAs($this->user)
            ->withHeaders([
                'X-LEDGER-ID' => $this->ledger->uuid,
            ])
            ->postJson("api/transactions/$transaction->uuid/cleared")
            ->assertOk();

        $transaction->refresh();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => true,
            'is_cleared'  => true
        ]);

        $this->assertNotNull($transaction->approved_at);
        $this->assertNotNull($transaction->cleared_at);
    }

    public function test_manually_approved_transaction_will_not_be_automatically_cleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->uncleared()
            ->create();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'is_cleared'  => false,
            'approved_at' => null,
            'cleared_at'  => null
        ]);

        $this->actingAs($this->user)
            ->withHeaders([
                'X-LEDGER-ID' => $this->ledger->uuid,
            ])
            ->postJson("api/transactions/$transaction->uuid/approved")
            ->assertOk();

        $transaction->refresh();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => true,
            'is_cleared'  => false,
            'cleared_at'  => null
        ]);

        $this->assertNotNull($transaction->approved_at);
    }

    public function test_account_balance_will_adjust_once_transaction_is_cleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->setInflow()
            ->state([
                'is_cleared' => false,
            ])
            ->create();

        $this->assertDatabaseHas('transactions', [
            'id'         => $transaction->id,
            'is_cleared' => false,
            'cleared_at' => null
        ]);

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid,])
            ->postJson("api/transactions/$transaction->uuid/cleared")
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance + $transaction->inflow,
        ]);
    }

    public function test_approved_transaction_but_not_cleared_will_not_adjust_account_balance(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->setInflow()
            ->state([
                'is_cleared'  => false,
                'is_approved' => false
            ])
            ->create();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_approved' => false,
            'is_cleared'  => false
        ]);

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson("api/transactions/$transaction->uuid/approved")
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

    public function test_account_balance_reverted_once_transaction_was_uncleared(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->setInflow()
            ->create();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_cleared'  => true,
            'is_approved' => true,
        ]);

        $this->actingAs($this->user)
            ->withHeaders([
                'X-LEDGER-ID' => $this->ledger->uuid,
            ])
            ->postJson("api/transactions/$transaction->uuid/uncleared")
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'is_cleared'  => false,
            'is_approved' => true,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance - $transaction->inflow,
        ]);
    }

    public function test_rejected_transaction_will_be_deleted(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->setInflow()
            ->state([
                'is_approved' => false,
            ])
            ->create();

        $this->actingAs($this->user)
            ->withHeaders([
                'X-LEDGER-ID' => $this->ledger->uuid,
            ])
            ->postJson("api/transactions/$transaction->uuid/rejected")
            ->assertOk();

        $this->assertSoftDeleted($transaction);
    }
}
