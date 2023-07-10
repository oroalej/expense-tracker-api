<?php

namespace Tests\Feature\Transaction;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class IndexTransactionTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        $this->url = "api/transactions";
    }

    public function test_guest_are_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_account_filter_is_working_properly(): void
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->cashAccountType()
            ->create();

        /** @var Collection $transactions */
        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($incomeCategory)
            ->state(new Sequence(
                ['account_id' => $anotherAccount->id],
                ['account_id' => $this->account->id],
            ))
            ->cleared()
            ->count(4)
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->account_id === $this->account->id)
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id)
            ])
            ->assertOk()
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_categories_filter_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'categories' => "invalid:content"
            ])
            ->assertJsonValidationErrors('categories');
    }

    public function test_assert_include_category_filter_is_working(): void
    {
        $categories = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->count(2)
            ->create()
            ->pluck('id');

        $toIncludeCategoryId = Hashids::encode($categories->get(1));

        /** @var Collection $transactions */
        $transactionIds = Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->state(
                new Sequence(
                    ['category_id' => $categories->get(0)],
                    ['category_id' => $categories->get(1)],
                )
            )
            ->cleared()
            ->count(4)
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->category_id === $categories->get(1))
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'categories' => "included:$toIncludeCategoryId"
            ])
            ->assertOk()
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_exclude_category_filter_is_working(): void
    {
        $categories = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->count(2)
            ->create()
            ->pluck('id');

        $toIncludeCategoryId = Hashids::encode($categories->get(1));

        /** @var Collection $transactions */
        $transactionIds = Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->state(
                new Sequence(
                    ['category_id' => $categories->get(0)],
                    ['category_id' => $categories->get(1)],
                )
            )
            ->cleared()
            ->count(4)
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->category_id !== $categories->get(1))
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'categories' => "excluded:$toIncludeCategoryId"
            ])
            ->assertOk()
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_amount_filter_is_valid()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "sample,1,1"
            ])
            ->assertJsonValidationErrors('amount');
    }

    public function test_assert_amount_is_equal_filter_is_working()
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->cleared()
            ->count(3)
            ->state(new Sequence(
                ['amount' => 1000],
                ['amount' => 750],
                ['amount' => 500],
            ))
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->amount === 500)
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "EQUAL,500"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_amount_is_greater_than_filter_is_working()
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->cleared()
            ->count(3)
            ->state(new Sequence(
                ['amount' => 1000],
                ['amount' => 750],
                ['amount' => 500],
            ))
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->amount > 500)
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "GT,600"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_amount_is_greater_than_or_equal_filter_is_working()
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->cleared()
            ->count(3)
            ->state(new Sequence(
                ['amount' => 1000],
                ['amount' => 750],
                ['amount' => 500],
            ))
            ->create()
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "GTE,500"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_amount_is_less_than_filter_is_working()
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->cleared()
            ->count(3)
            ->state(new Sequence(
                ['amount' => 1000],
                ['amount' => 750],
                ['amount' => 500],
            ))
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->amount === 500)
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "LT,700"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_amount_is_less_than_or_equal_filter_is_working()
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->cleared()
            ->count(3)
            ->state(new Sequence(
                ['amount' => 1000],
                ['amount' => 750],
                ['amount' => 500],
            ))
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->amount <= 750)
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "LTE,750"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_amount_is_between_filter_is_working()
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($incomeCategory)
            ->cleared()
            ->count(3)
            ->state(new Sequence(
                ['amount' => 1000],
                ['amount' => 750],
                ['amount' => 500],
            ))
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->amount === 1000)
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'amount'     => "BETWEEN,900,1100"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_sort_filter_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'sort'       => "invalid_colum:ascending"
            ])
            ->assertJsonValidationErrors('sort');
    }

    public function test_assert_sort_desc_is_working(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->cleared()
            ->count(4)
            ->create()
            ->sortBy([
                ['transaction_date', 'desc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'sort'       => "transaction_date:desc"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_sort_asc_is_working(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->cleared()
            ->count(4)
            ->create()
            ->sortBy([
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'sort'       => "transaction_date:asc"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_sort_multiple_is_working(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->cleared()
            ->count(4)
            ->create()
            ->sortBy([
                ['transaction_date', 'asc'],
                ['category_id', 'desc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'sort'       => "transaction_date:asc,category_id:desc"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_unapproved_transaction_should_be_at_the_top(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->count(2)
            ->state(new Sequence(
                ['is_approved' => false, 'is_cleared' => true],
                ['is_approved' => true, 'is_cleared' => true]
            ))
            ->create()
            ->sortBy([
                ['is_approved', 'asc'],
                ['is_cleared', 'asc'],
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'sort'       => "transaction_date:asc,category_id:desc"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_result_should_be_in_correct_order(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->count(3)
            ->state(new Sequence(
                ['is_approved' => false, 'is_cleared' => true],
                ['is_approved' => true, 'is_cleared' => true],
                ['is_approved' => true, 'is_cleared' => false],
            ))
            ->create()
            ->sortBy([
                ['is_approved', 'asc'],
                ['is_cleared', 'asc'],
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'sort'       => "transaction_date:asc,category_id:desc"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_state_filter_works(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'state'      => "something"
            ])
            ->assertJsonValidationErrors('state');
    }

    public function test_assert_only_returns_transaction_for_approval(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->count(4)
            ->state(new Sequence(
                ['is_approved' => false, 'is_cleared' => false],
                ['is_approved' => true, 'is_cleared' => true],
            ))
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->is_approved === false)
            ->sortBy([
                ['is_approved', 'asc'],
                ['is_cleared', 'asc'],
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'state'      => "action"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_assert_only_returns_transaction_for_clear(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $transactionIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->account)
            ->for($category)
            ->count(4)
            ->state(new Sequence(
                ['is_approved' => false, 'is_cleared' => false],
                ['is_approved' => true, 'is_cleared' => false],
            ))
            ->create()
            ->filter(fn (
                Transaction $transaction
            ) => $transaction->is_cleared === false && $transaction->is_approved === true)
            ->sortBy([
                ['is_approved', 'asc'],
                ['is_cleared', 'asc'],
                ['transaction_date', 'asc'],
                ['created_at', 'asc']
            ])
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id),
                'state'      => "clear"
            ])
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($transactionIds, $responseIds);
    }

    public function test_api_has_correct_structure(): void
    {
        /** @var Category $incomeCategory */
        $incomeCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($incomeCategory)
            ->cleared()
            ->count(2)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure([
                    'paginated' => $this->apiStructurePaginated([
                        'id',
                        'category_id',
                        'account_id',
                        'ledger_id',
                        'remarks',
                        'amount',
                        'transaction_date',
                        'is_approved',
                        'is_cleared',
                    ]),
                ])
            );
    }
}
