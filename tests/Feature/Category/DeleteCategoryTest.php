<?php

namespace Tests\Feature\Category;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class DeleteCategoryTest extends TestCase
{
    public Category $category;
    public Account  $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::first())
            ->create();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $categoryId = Hashids::encode($this->category->id);

        $this->url = "api/categories/$categoryId";
    }

    public function test_guest_now_allowed(): void
    {
        $this->deleteJson($this->url)
            ->assertUnauthorized();
    }

    public function test_a_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()
            ->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertNotFound();
    }

    public function test_category_id_is_optional_when_deleting_category_do_not_have_transactions(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertJsonMissingValidationErrors('category_id');
    }

    public function test_category_id_is_required_when_destination_category_have_transactions(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_valid(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Str::random()
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_not_the_same_as_destination_category(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($this->category->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_assert_category_and_destination_category_has_the_same_category_type()
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->create();

        /** @var Category $destinationCategory */
        $destinationCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($destinationCategory->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_under_the_same_ledger(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->create();

        $ledger = Ledger::factory()
            ->for(Currency::first())
            ->for($this->user)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($ledger)
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($category->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_transactions_are_transferred_to_destination_category()
    {
        /** @var Collection<Transaction> $transactions */
        $transactions = Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->count(2)
            ->create();

        /** @var Category $destinationCategory */
        $destinationCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $transactions->each(function (Transaction $transaction) {
            $this->assertDatabaseHas('transactions', [
                'id'          => $transaction->id,
                'category_id' => $this->category->id
            ]);
        });

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($destinationCategory->id)
            ])
            ->assertOk();

        $transactions->each(function (Transaction $transaction) use ($destinationCategory) {
            $this->assertDatabaseHas('transactions', [
                'id'          => $transaction->id,
                'category_id' => $destinationCategory->id
            ]);
        });
    }

    public function test_assert_budget_category_are_adjusted(): void
    {
        /** @var Category $destinationCategory */
        $destinationCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->state(new Sequence(
                [
                    'transaction_date' => Carbon::now()->subMonth(),
                    'amount'           => 4000,
                ],
                [
                    'transaction_date' => Carbon::now(),
                    'amount'           => 1700
                ]
            ))
            ->count(4)
            ->cleared()
            ->create();

        $budget1 = Budget::getDataByDateAndLedger(Carbon::now()->subMonth(), $this->ledger);
        $budget2 = Budget::getDataByDateAndLedger(Carbon::now(), $this->ledger);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($destinationCategory->id)
            ])
            ->assertOk();

        $this->assertDatabaseHas('budget_categories', [
            'budget_id'   => $budget1->id,
            'category_id' => $destinationCategory->id,
            'activity'    => -8000,
            'available'   => -8000,
            'assigned'    => 0,
        ]);

        $this->assertDatabaseHas('budget_categories', [
            'budget_id'   => $budget2->id,
            'category_id' => $destinationCategory->id,
            'activity'    => -3400,
            'available'   => -3400,
            'assigned'    => 0,
        ]);
    }
}
