<?php

namespace Tests\Feature\Ledger;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class DestroyLedgerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $ledgerId  = Hashids::encode($this->ledger->id);
        $this->url = "api/ledgers/$ledgerId";
    }

    public function test_guest_not_allowed(): void
    {
        $this->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertUnauthorized();
    }

    public function test_a_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_ledger_is_deleted(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertSoftDeleted('ledgers', [
            'id'   => $this->ledger->id,
        ]);
    }

    public function test_assert_that_the_transactions_were_deleted(): void
    {
        /** @var Account $account */
        $account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::first())
            ->create();

        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($categoryGroup)
            ->for($this->ledger)
            ->create();

        /** @var Transaction $transaction */
        $transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($account)
            ->for($category)
            ->create();

        $this->assertNotSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertSoftDeleted('transactions', [
            'ledger_id' => $this->ledger->id,
        ]);
    }

    public function test_assert_that_the_accounts_were_deleted(): void
    {
        /** @var Account $account */
        $account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::first())
            ->create();

        $this->assertNotSoftDeleted('accounts', [
            'id' => $account->id,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertSoftDeleted('accounts', [
            'ledger_id' => $this->ledger->id,
        ]);
    }

    public function test_assert_that_the_categories_and_category_groups_were_deleted(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($categoryGroup)
            ->for($this->ledger)
            ->create();

        $this->assertNotSoftDeleted('category_groups', [
            'id' => $categoryGroup->id,
        ]);

        $this->assertNotSoftDeleted('categories', [
            'id' => $category->id,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertSoftDeleted('category_groups', [
            'ledger_id' => $this->ledger->id,
        ]);

        $this->assertSoftDeleted('categories', [
            'ledger_id' => $this->ledger->id,
        ]);
    }

    public function test_assert_that_the_budgets_were_deleted(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        /** @var Category $category */
        $category = Category::factory()
            ->for($categoryGroup)
            ->for($this->ledger)
            ->create();

        /** @var Budget $budget */
        $budget = Budget::factory()
            ->for($this->ledger)
            ->create();

        BudgetCategory::factory()
            ->for($budget)
            ->for($category)
            ->create();

        $this->assertNotSoftDeleted('budgets', [
            'id' => $budget->id,
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->deleteJson($this->url)
            ->assertOk();

        $this->assertSoftDeleted('budgets', [
            'ledger_id' => $this->ledger->id,
        ]);
    }
}
