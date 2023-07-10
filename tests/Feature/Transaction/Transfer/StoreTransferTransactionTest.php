<?php

namespace Tests\Feature\Transaction\Transfer;

use App\Enums\DefaultCategoryIDs;
use App\Models\Account;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreTransferTransactionTest extends TestCase
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

    public function test_assert_transfer_transaction_requires_transfer_account(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode(DefaultCategoryIDs::TRANSFER->value),
            ])
            ->assertJsonValidationErrors('transfer_id');
    }

    public function test_assert_transfer_id_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode(DefaultCategoryIDs::TRANSFER->value),
                'transfer_id' => 99999999
            ])
            ->assertJsonValidationErrors('transfer_id');
    }

    public function test_assert_transfer_id_is_own_and_same_ledger()
    {
        $nowOwnLedger = Ledger::factory()
            ->for(User::factory())
            ->for(Currency::first())
            ->create();

        /** @var Account $notTransferAccount */
        $notTransferAccount = Account::factory()
            ->for($nowOwnLedger)
            ->cashAccountType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode(DefaultCategoryIDs::TRANSFER->value),
                'transfer_id' => Hashids::encode($notTransferAccount->id)
            ])
            ->assertJsonValidationErrors('transfer_id');
    }

    public function test_user_can_transfer_transaction(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'transfer_id'      => Hashids::encode($anotherAccount->id),
            'remarks'          => $this->faker->word,
            'transaction_date' => $this->faker->date,
            'category_id'      => Hashids::encode(DefaultCategoryIDs::TRANSFER->value),
            'amount'           => 10000
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'account_id'  => $this->account->id,
            'category_id' => DefaultCategoryIDs::TRANSFER->value,
            'remarks'     => $attributes['remarks'],
            'amount'      => $attributes['amount'],
        ]);
    }

    public function test_account_balance_was_updated_after_transfer_transaction_created(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->setCurrentBalance(500)
            ->create();

        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'transfer_id'      => Hashids::encode($anotherAccount->id),
            'remarks'          => $this->faker->word,
            'transaction_date' => $this->faker->date,
            'category_id'      => Hashids::encode(DefaultCategoryIDs::TRANSFER->value),
            'amount'           => 10000
        ];

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => 0
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => 500
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => -10000
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'              => $anotherAccount->id,
            'current_balance' => 10500
        ]);
    }
}
