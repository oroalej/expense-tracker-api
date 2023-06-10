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

class UpdateAccountTransactionTest extends TestCase
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
            ->state([
                'current_balance' => 0
            ])
            ->create();

        $this->category = Category::factory()
            ->for(
                CategoryGroup::factory()->for($this->ledger)
            )
            ->for($this->ledger)
            ->create();
    }

    public function test_account_balance_is_correctly_updated_when_outflow_was_changed(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setOutflow(2000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);
        $url           = "api/transactions/$transactionId";

        $attributes = [
            'outflow'          => 3000,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => Hashids::encode($transaction->category->id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -3000,
        ]);
    }

    public function test_account_balance_is_correctly_updated_when_inflow_was_changed(): void
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow(2000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);
        $url           = "api/transactions/$transactionId";

        $attributes = [
            'inflow'           => 8000,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => Hashids::encode($transaction->category->id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 8000
        ]);
    }

    public function test_account_balance_is_correctly_updated_when_account_id_was_changed()
    {
        $accountType = AccountType::find(AccountTypeState::Cash->value);

        /** @var Account $oldAccount */
        $oldAccount = Account::factory()
            ->for($this->ledger)
            ->for($accountType)
            ->state([
                'current_balance' => 50
            ])
            ->create();

        /** @var Account $newAccount */
        $newAccount = Account::factory()
            ->for($this->ledger)
            ->for($accountType)
            ->state([
                'current_balance' => 20
            ])
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($oldAccount)
            ->for($this->category)
            ->for($this->ledger)
            ->setInflow(3000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($transaction->id);
        $url           = "api/transactions/$transactionId";

        $attributes = [
            'inflow'           => $transaction->inflow,
            'remarks'          => $transaction->remarks,
            'transaction_date' => $transaction->transaction_date->format('Y-m-d'),
            'category_id'      => Hashids::encode($transaction->category->id),
            'account_id'       => Hashids::encode($newAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $oldAccount->id,
            'current_balance' => 3050
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $oldAccount->id,
            'current_balance' => 50
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $newAccount->id,
            'current_balance' => 3020
        ]);
    }
}
