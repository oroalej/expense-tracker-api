<?php

namespace Tests\Feature\Transaction;

use App\Enums\CategoryTypeState;
use App\Enums\WalletAccessTypeState;
use App\Enums\WalletTypeState;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class UpdateTransactionWalletBalanceTest extends TestCase
{
    public string $url;

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }

    public function test_asserts_wallet_balance_is_updated_when_category_was_changed_from_expense_to_income(): void
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Income)
            ->create();

        [$transaction, $wallet] = $this->generateTransaction();

        $attributes = [
            'amount' => $transaction->amount,
            'remarks' => $transaction->remarks,
            'transaction_date' => $this->faker->date,
            'wallet_id' => $wallet->id,
            'category_id' => $incomeCategory->id,
        ];

        $expectedCurrentBalance =
            $wallet->current_balance + 2 * $transaction->amount;

        $this->putJson(
            "api/transaction/$transaction->uuid",
            $attributes
        )->assertOk();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'current_balance' => $expectedCurrentBalance,
        ]);
    }

    public function test_asserts_wallet_balance_is_updated_when_category_was_changed_from_income_to_expense(): void
    {
        /** @var Category $expenseCategory */
        $expenseCategory = Category::factory()
            ->for($this->user)
            ->setCategoryType(CategoryTypeState::Expense)
            ->create();

        [$transaction, $wallet] = $this->generateTransaction(
            CategoryTypeState::Income
        );

        $attributes = [
            'amount' => $transaction->amount,
            'remarks' => $transaction->remarks,
            'transaction_date' => $this->faker->date,
            'wallet_id' => $wallet->id,
            'category_id' => $expenseCategory->id,
        ];

        $expectedCurrentBalance =
            $wallet->current_balance - 2 * $transaction->amount;

        $this->putJson(
            "api/transaction/$transaction->uuid",
            $attributes
        )->assertOk();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'current_balance' => $expectedCurrentBalance,
        ]);
    }

    public function test_asserts_wallet_balance_is_updated_when_amount_changes_from_expense_transaction(): void
    {
        [$transaction, $wallet, $category] = $this->generateTransaction();

        $attributes = [
            'amount' => $this->faker->numberBetween(),
            'remarks' => $transaction->remarks,
            'transaction_date' => $this->faker->date,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
        ];

        $expectedCurrentBalance =
            $wallet->current_balance +
            $transaction->amount -
            $attributes['amount'];

        $this->putJson(
            "api/transaction/$transaction->uuid",
            $attributes
        )->assertOk();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'current_balance' => $expectedCurrentBalance,
        ]);
    }

    public function test_asserts_wallet_balance_is_updated_when_amount_changes_from_income_transaction(): void
    {
        [$transaction, $wallet, $category] = $this->generateTransaction(
            CategoryTypeState::Income
        );

        $attributes = [
            'amount' => $this->faker->numberBetween(),
            'remarks' => $transaction->remarks,
            'transaction_date' => $this->faker->date,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
        ];

        $expectedCurrentBalance =
            $wallet->current_balance -
            $transaction->amount +
            $attributes['amount'];

        $this->putJson(
            "api/transaction/$transaction->uuid",
            $attributes
        )->assertOk();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'current_balance' => $expectedCurrentBalance,
        ]);
    }

    public function test_asserts_wallet_balance_is_updated_when_wallet_was_changed_from_expense_transaction(): void
    {
        [$transaction, $wallet, $category] = $this->generateTransaction();

        /** @var Wallet $anotherWallet */
        $anotherWallet = Wallet::factory()
            ->setWalletType(WalletTypeState::Cash)
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();

        $attributes = [
            'amount' => $transaction->amount,
            'remarks' => $transaction->remarks,
            'transaction_date' => $this->faker->date,
            'wallet_id' => $anotherWallet->id,
            'category_id' => $category->id,
        ];

        $this->putJson(
            "api/transaction/$transaction->uuid",
            $attributes
        )->assertOk();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'current_balance' =>
                $wallet->current_balance + $transaction->amount,
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $anotherWallet->id,
            'current_balance' =>
                $anotherWallet->current_balance - $transaction->amount,
        ]);
    }

    public function test_asserts_wallet_balance_is_updated_when_wallet_changes_from_income_transaction(): void
    {
        [$transaction, $wallet, $category] = $this->generateTransaction(
            CategoryTypeState::Income
        );

        /** @var Wallet $anotherWallet */
        $anotherWallet = Wallet::factory()
            ->setWalletType(WalletTypeState::Cash)
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();

        $attributes = [
            'amount' => $transaction->amount,
            'remarks' => $transaction->remarks,
            'transaction_date' => $this->faker->date,
            'wallet_id' => $anotherWallet->id,
            'category_id' => $category->id,
        ];

        $this->putJson(
            "api/transaction/$transaction->uuid",
            $attributes
        )->assertOk();

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'current_balance' =>
                $wallet->current_balance - $transaction->amount,
        ]);

        $this->assertDatabaseHas('wallets', [
            'id' => $anotherWallet->id,
            'current_balance' =>
                $anotherWallet->current_balance + $transaction->amount,
        ]);
    }

    private function generateTransaction(
        CategoryTypeState $categoryType = CategoryTypeState::Expense
    ): array {
        /** @var Wallet $wallet */
        $wallet = Wallet::factory()
            ->setWalletType(WalletTypeState::Cash)
            ->hasAttached($this->user, [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($this->user)
            ->setCategoryType($categoryType)
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->user)
            ->for($category)
            ->for($wallet)
            ->create();

        return [$transaction, $wallet, $category];
    }
}
