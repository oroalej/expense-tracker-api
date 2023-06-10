<?php

namespace Tests\Feature\Transaction;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class IndexTransactionTest extends TestCase
{
    public Account     $account;
    public Category    $category;
    public AccountType $cashAccountType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashAccountType = AccountType::find(AccountTypeState::Cash->value);

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for($this->cashAccountType)
            ->create();

        $this->category = Category::factory()
            ->for(
                CategoryGroup::factory()->for($this->ledger)
            )
            ->for($this->ledger)
            ->create();

        $this->url = "api/transactions";
    }

    public function test_guest_are_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_account_filter_is_working_properly(): void
    {
        /** @var Account $anotherAccount */
        $anotherAccount = Account::factory()
            ->for($this->ledger)
            ->for($this->cashAccountType)
            ->create();

        /** @var Collection $transactions */
        $filteredIds = Transaction::factory()
            ->for($this->ledger)
            ->for($this->category)
            ->cleared()
            ->state(new Sequence(
                ['account_id' => $anotherAccount->id],
                ['account_id' => $this->account->id]
            ))
            ->count(2)
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->account_id === $this->account->id)
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'account_id' => Hashids::encode($this->account->id)
            ])
            ->assertOk()
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($filteredIds, $responseIds);
    }

    public function test_category_filter_is_working_properly(): void
    {
        /** @var Category $anotherCategory */
        $anotherCategory = Category::factory()
            ->for($this->category->categoryGroup)
            ->for($this->ledger)
            ->create();

        /** @var Collection $transactions */
        $filteredIds = Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->cleared()
            ->state(new Sequence(
                ['category_id' => $anotherCategory->id],
                ['category_id' => $this->category->id]
            ))
            ->count(2)
            ->create()
            ->filter(fn (Transaction $transaction) => $transaction->category_id === $this->category->id)
            ->pluck('id')
            ->toArray();

        $responseIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, [
                'category_id' => Hashids::encode($this->category->id)
            ])
            ->assertOk()
            ->getOriginalContent()['result']['paginated']->values()->pluck('id')->toArray();

        $this->assertEquals($filteredIds, $responseIds);
    }

    public function test_api_has_correct_structure(): void
    {
        Transaction::factory()
            ->for($this->account)
            ->for($this->ledger)
            ->for($this->category)
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
                        'outflow',
                        'inflow',
                        'transaction_date',
                        'is_approved',
                        'is_cleared',
                    ]),
                    'summary'   => [
                        'uncleared_balance',
                        'cleared_balance',
                        'working_balance'
                    ]
                ])
            );
    }
}
