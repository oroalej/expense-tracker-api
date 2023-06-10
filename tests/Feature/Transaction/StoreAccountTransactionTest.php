<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreAccountTransactionTest extends TestCase
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
            ->for($this->ledger)
            ->for(
                CategoryGroup::factory()->for($this->ledger)
            )
            ->create();

        $this->url = "api/transactions";
    }

    public function test_account_balance_is_deducted_when_outflow_is_filled(): void
    {
        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($this->category->id),
            'remarks'          => $this->faker->word,
            'outflow'          => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => true,
            'is_approved'      => true
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance - $attributes['outflow'],
        ]);
    }

    public function test_account_balance_is_added_when_inflow_is_filled(): void
    {
        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($this->category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => true,
            'is_approved'      => true
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance + $attributes['inflow'],
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_not_cleared_and_approved(): void
    {
        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($this->category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => false,
            'is_approved'      => false,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_is_not_deducted_when_transaction_is_not_approved_and_cleared(): void
    {
        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($this->category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => $this->faker->numberBetween(1, 999999),
            'transaction_date' => $this->faker->date,
            'is_cleared'       => true,
            'is_approved'      => false,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance,
        ]);
    }

    public function test_account_balance_will_adjust_when_transaction_is_cleared_and_approved()
    {
        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($this->category->id),
            'remarks'          => $this->faker->word,
            'inflow'           => 1000,
            'transaction_date' => $this->faker->date,
            'is_cleared'       => true,
            'is_approved'      => true,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('accounts', [
            'id'              => $this->account->id,
            'current_balance' => $this->account->current_balance + $attributes['inflow'],
        ]);
    }
}
