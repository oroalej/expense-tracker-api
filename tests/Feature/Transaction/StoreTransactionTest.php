<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use Illuminate\Database\Schema\Builder;
use Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::find(AccountTypeState::Cash->value))
            ->create();

        $this->url = "api/transactions";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_account_id_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('account_id');
    }

    public function test_account_id_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['account_id' => 999999999])
            ->assertJsonValidationErrors('account_id');
    }

    public function test_category_id_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['category_id' => 999999999])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_remarks_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('remarks');
    }

    public function test_assert_remarks_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'remarks' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('remarks');
    }

    public function test_assert_transaction_date_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_assert_transaction_date_is_in_correct_format(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['transaction_date' => '12-31-2022'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_assert_transaction_date_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['transaction_date' => '2022-13-31'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_assert_is_approved_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_approved');
    }

    public function test_assert_is_cleared_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_cleared');
    }

    public function test_assert_amount_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['amount' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('amount');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['amount' => null])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accept_positive_number()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'amount' => -1000
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accepts_whole_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'amount' => 99.11,
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accept_13_digits(): void
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        /** @var Category $expenseCategory */
        $expenseCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode($incomeCategory->id),
                'amount'      => 99999999999999,
            ])
            ->assertJsonValidationErrors('amount');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode($expenseCategory->id),
                'amount'      => 99999999999999,
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_api_has_correct_structure(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $attributes = [
            'account_id'       => Hashids::encode($this->account->id),
            'category_id'      => Hashids::encode($category->id),
            'remarks'          => $this->faker->word,
            'amount'           => 1234,
            'transaction_date' => $this->faker->date,
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated()
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'ledger_id',
                    'category_id',
                    'account_id',
                    'remarks',
                    'amount',
                    'transaction_date',
                    'is_approved',
                    'is_cleared',
                    'created_at',
                    'updated_at',
                ])
            );
    }
}
