<?php

namespace Tests\Feature\CategoryGroup;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class DestroyCategoryGroupTest extends TestCase
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

        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->for($categoryGroup)
            ->create();

        $categoryGroupId = Hashids::encode($categoryGroup->id);

        $this->url = "api/category-groups/$categoryGroupId";
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
            ->deleteJson($this->url)
            ->assertNotFound();
    }

    public function test_category_id_is_optional_when_categories_do_not_have_transactions(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertJsonMissingValidationErrors('category_id');
    }

    public function test_category_id_is_valid(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Str::random()
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_category_id_is_required_when_categories_have_transactions(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_selected_category_id_is_not_under_deleting_category_group(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($this->category->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }


    public function test_category_id_is_under_the_same_ledger(): void
    {
        $ledger = Ledger::factory()
            ->for(Currency::first())
            ->for($this->user)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for(CategoryGroup::factory()->for($ledger))
            ->for($ledger)
            ->create();

        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($category->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_categories_under_the_deleted_category_group_were_also_deleted(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertSoftDeleted('categories', [
            'id' => $this->category->id
        ]);
    }

    public function test_transactions_of_categories_under_the_category_group_were_transferred_to_selected_category(
    ): void {
        /** @var Category $destinationCategory */
        $destinationCategory = Category::factory()
            ->for($this->ledger)
            ->for(
                CategoryGroup::factory()
                    ->for($this->ledger)
            )
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->create();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'category_id' => $this->category->id
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($destinationCategory->id)
            ])
            ->assertOk();

        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'category_id' => $destinationCategory->id
        ]);
    }

    public function test_budget_category_are_adjusted(): void
    {
        /** @var Category $destinationCategory */
        $destinationCategory = Category::factory()
            ->for($this->ledger)
            ->for(
                CategoryGroup::factory()
                    ->for($this->ledger)
            )
            ->create();

        /** @var Transaction $transaction */
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
            ->state(new Sequence(
                [
                    'transaction_date' => Carbon::now()->subMonth(),
                    'inflow'           => 3000,
                    'outflow'          => 0
                ],
                [
                    'transaction_date' => Carbon::now(),
                    'inflow'           => 0,
                    'outflow'          => 1300
                ]
            ))
            ->count(4)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url, [
                'category_id' => Hashids::encode($destinationCategory->id)
            ])
            ->assertOk();

        $budget1 = Budget::getDataByDateAndLedger(Carbon::now()->subMonth(), $this->ledger);
        $budget2 = Budget::getDataByDateAndLedger(Carbon::now(), $this->ledger);

        $this->assertDatabaseHas('budget_categories', [
            'budget_id'   => $budget1['id'],
            'category_id' => $destinationCategory->id,
            'activity'    => 6000,
            'available'   => 6000,
            'assigned'    => 0,
        ]);

        $this->assertDatabaseHas('budget_categories', [
            'budget_id'   => $budget2['id'],
            'category_id' => $destinationCategory->id,
            'activity'    => -2600,
            'available'   => -2600,
            'assigned'    => 0,
        ]);
    }
}
