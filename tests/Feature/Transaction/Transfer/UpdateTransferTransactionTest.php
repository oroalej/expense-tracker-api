<?php

namespace Tests\Feature\Transaction\Transfer;

use App\Enums\DefaultCategoryIDs;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateTransferTransactionTest extends TestCase
{
    protected Account     $account;
    protected Account     $transferAccount;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(300)
            ->create();

        $this->transferAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(700)
            ->create();

        $this->transaction = Transaction::factory()
            ->for(Category::find(DefaultCategoryIDs::TRANSFER->value))
            ->for($this->ledger)
            ->for($this->account)
            ->for($this->transferAccount, 'transfer')
            ->setAmount(2000)
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($this->transaction->id);

        $this->url = "api/transactions/$transactionId";
    }

    public function test_assert_account_current_balance_is_updated_when_amount_changed(): void
    {
        $attributes = [
            'amount'           => 4500,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
            'transfer_id'      => Hashids::encode($this->transaction->transfer_id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -1700
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 2700
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -4200
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 5200
        ]);
    }

    public function test_assert_old_account_is_reverted_when_account_is_changed(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(1000)
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($anotherAccount->id),
            'transfer_id'      => Hashids::encode($this->transaction->transfer_id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => 1000
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -1700
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 2700
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 300
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => -1000
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 2700
        ]);
    }

    public function test_assert_old_transfer_account_is_reverted_when_transfer_account_is_changed(): void
    {
        /** @var Account $anotherTransferAccount */
        $anotherTransferAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(1200)
            ->create();

        $attributes = [
            'amount'           => $this->transaction->amount,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
            'transfer_id'      => Hashids::encode($anotherTransferAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -1700
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherTransferAccount->id,
            'current_balance' => 1200
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 2700
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -1700
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherTransferAccount->id,
            'current_balance' => 3200
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 700
        ]);
    }

    public function test_assert_accounts_are_updated_when_amount_original_account_and_target_account_are_changed(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(800)
            ->create();

        /** @var Account $anotherTransferAccount */
        $anotherTransferAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(1200)
            ->create();

        $attributes = [
            'amount'           => 3000,
            'remarks'          => $this->transaction->remarks,
            'transaction_date' => $this->transaction->transaction_date->toDateString(),
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($anotherAccount->id),
            'transfer_id'      => Hashids::encode($anotherTransferAccount->id),
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -1700
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 300
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 700
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => -2200
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherTransferAccount->id,
            'current_balance' => 4200
        ]);
    }

    public function test_assert_accounts_are_reverted_when_category_is_changed(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
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
            'current_balance' => -1700
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 2700
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 2300
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->transferAccount->id,
            'current_balance' => 700
        ]);
    }
}
