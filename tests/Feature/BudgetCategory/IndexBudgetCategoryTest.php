<?php

namespace Tests\Feature\BudgetCategory;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class IndexBudgetCategoryTest extends TestCase
{
    public Transaction $transaction;
    public Category    $category;
    public Account     $account;
    public Budget      $budget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->for(CategoryGroup::factory()->for($this->ledger))
            ->create();

        $this->account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::first())
            ->create();

        /** @var Transaction $transaction */
        $this->transaction = Transaction::factory()
            ->for($this->ledger)
            ->for($this->category)
            ->for($this->account)
            ->setInflow(2000)
            ->cleared()
            ->create();

        $this->budget = Budget::getDataByDateAndLedger($this->transaction->transaction_date, $this->ledger);
        $hashBudgetId = Hashids::encode($this->budget->id);

        $this->url = "api/budgets/$hashBudgetId/budget-categories";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->getJson($this->url)
            ->assertUnauthorized();
    }

    public function test_response_data_is_correct(): void
    {
        $transactionDate = $this->transaction
            ->transaction_date
            ->format('Y-m-d');

        /** @var Category $anotherCategory */
        $anotherCategory = Category::factory()
            ->for($this->ledger)
            ->for($this->category->categoryGroup)
            ->create();

        /** @var Category $anotherCategoryWithDifferentGroup */
        $anotherCategoryWithDifferentGroup = Category::factory()
            ->for($this->ledger)
            ->for(CategoryGroup::factory()->for($this->ledger))
            ->create();

        Transaction::factory()
            ->for($this->ledger)
            ->for($this->category)
            ->for($this->account)
            ->setOutflow(5000)
            ->cleared()
            ->state(new Sequence(
                [    // Different Category.
                    'category_id'      => $anotherCategory->id,
                    'transaction_date' => $transactionDate
                ],
                [ // Category from another group.
                'category_id'      => $anotherCategoryWithDifferentGroup->id,
                'transaction_date' => $transactionDate
            ],
                [ // The category we are validating.
                'category_id'      => $this->category->id,
                'transaction_date' => $transactionDate
            ],
                [ // Transaction from last month.
                'category_id'      => $this->category->id,
                'transaction_date' => $this->transaction
                    ->transaction_date
                    ->subMonth()
                    ->format('Y-m-d')
            ],
            ))
            ->count(4)
            ->create();

        $response = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->getContent();

        $response        = json_decode($response);
        $transactionId   = Hashids::encode($this->transaction->id);
        $categoryGroupId = Hashids::encode($this->category->category_group_id);

        /** @var BudgetCategory $category */
        $category      = collect($response->result->categories)->firstWhere('id', $transactionId);
        $categoryGroup = collect($response->result->category_groups)->firstWhere('id', $categoryGroupId);

        // Category
        $this->assertEquals([
            'category_id' => Hashids::encode($this->category->id),
            'budget_id'   => Hashids::encode($this->budget->id),
            'available'   => -3000,
            'activity'    => -3000,
            'assigned'    => 0
        ], [
            'category_id' => $category->category_id,
            'budget_id'   => $category->budget_id,
            'available'   => $category->available,
            'activity'    => $category->activity,
            'assigned'    => $category->assigned
        ]);

        // Category Group
        $this->assertEquals([
            'id'        => $categoryGroupId,
            'available' => -8000,
            'activity'  => -8000,
            'assigned'  => 0
        ], [
            'id'        => $categoryGroup->id,
            'available' => $categoryGroup->available,
            'activity'  => $categoryGroup->activity,
            'assigned'  => $categoryGroup->assigned
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure([
                    'categories'      => $this->apiStructureCollection([
                        'id',
                        'category_id',
                        'budget_id',
                        'available',
                        'assigned',
                        'activity',
                        'transactions' => $this->apiStructureCollection([
                            'id',
                            'account_id',
                            'transaction_date',
                            'remarks',
                            'inflow',
                            'outflow'
                        ])
                    ]),
                    'category_groups' => $this->apiStructureCollection([
                        'id',
                        'available',
                        'assigned',
                        'activity'
                    ])
                ])
            );
    }
}
