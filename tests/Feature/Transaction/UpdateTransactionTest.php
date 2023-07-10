<?php

namespace Tests\Feature\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateTransactionTest extends TestCase
{
    public Transaction $transaction;
    public Account     $account;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Account $account */
        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        $this->transaction = Transaction::factory()
            ->for(
                Category::factory()
                    ->for($this->ledger)
                    ->incomeType()
            )
            ->for($this->ledger)
            ->for($this->account)
            ->setAmount()
            ->cleared()
            ->create();

        $transactionId = Hashids::encode($this->transaction->id);
        $this->url     = "api/transactions/$transactionId";
    }

    public function test_guest_are_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_user_can_only_access_own_data(): void
    {
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_account_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('account_id');
    }

    public function test_account_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['account_id' => 999999999])
            ->assertJsonValidationErrors('account_id');
    }

    public function test_category_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['category_id' => 999999999])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_remarks_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('remarks');
    }

    public function test_remarks_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'remarks' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('remarks');
    }

    public function test_transaction_date_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_in_correct_format(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['transaction_date' => '12-31-2022'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_transaction_date_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['transaction_date' => '2022-13-31'])
            ->assertJsonValidationErrors('transaction_date');
    }

    public function test_is_approved_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('is_approved');
    }

    public function test_is_cleared_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonMissingValidationErrors('is_cleared');
    }

    public function test_assert_amount_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['amount' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('amount');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, ['amount' => null])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accept_positive_number()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'amount' => -1000
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_only_accepts_whole_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
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
            ->putJson($this->url, [
                'category_id' => Hashids::encode($incomeCategory->id),
                'amount'      => 99999999999999,
            ])
            ->assertJsonValidationErrors('amount');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'category_id' => Hashids::encode($expenseCategory->id),
                'amount'      => 99999999999999,
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_user_can_update_own_transaction(): void
    {
        $attributes = [
            'amount'           => 1000,
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->transaction->account_id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $this->transaction->id,
            'account_id'  => $this->account->id,
            'category_id' => $this->transaction->category_id,
            'amount'      => $attributes['amount'],
        ]);
    }

    public function test_assert_changes_reflected(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        /** @var Category $anotherCategory */
        $anotherCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $attributes = [
            'amount'           => 1000,
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id'      => Hashids::encode($anotherCategory->id),
            'account_id'       => Hashids::encode($anotherAccount->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'               => $this->transaction->id,
            'account_id'       => $anotherAccount->id,
            'category_id'      => $anotherCategory->id,
            'amount'           => $attributes['amount'],
            'remarks'          => $attributes['remarks'],
            'transaction_date' => Carbon::parse($attributes['transaction_date'])->toDateTimeString()
        ]);
    }

    public function test_assert_api_structure_is_correct(): void
    {
        $attributes = [
            'amount'           => 1000,
            'remarks'          => $this->faker->sentence,
            'transaction_date' => $this->faker->date,
            'category_id'      => Hashids::encode($this->transaction->category_id),
            'account_id'       => Hashids::encode($this->account->id),
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk()
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
