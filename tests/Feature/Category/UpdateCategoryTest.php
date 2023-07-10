<?php

namespace Tests\Feature\Category;

use App\Enums\CategoryTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateCategoryTest extends TestCase
{
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $categoryId = Hashids::encode($this->category->id);

        $this->url = "api/categories/$categoryId";
    }

    public function test_guest_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_you_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(256),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_notes_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'notes' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_assert_notes_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'notes' => Str::random(256),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_asserts_category_type_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('category_type');
    }

    public function test_asserts_category_type_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'category_type' => 1234
            ])
            ->assertJsonValidationErrors('category_type');
    }

    public function test_asserts_category_type_can_only_be_changed_when_no_transactions(): void
    {
        $account = Account::factory()
            ->for($this->ledger)
            ->for(AccountType::first())
            ->create();

        Transaction::factory()
            ->for($this->ledger)
            ->for($account)
            ->for($this->category)
            ->setAmount()
            ->cleared()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'category_type' => CategoryTypeState::EXPENSE->value
            ])
            ->assertJsonValidationErrors('category_type');

        $this->assertDatabaseHas('categories', [
            'id'            => $this->category->id,
            'category_type' => $this->category->category_type
        ]);
    }

    public function test_asserts_parent_is_own_and_same_ledger(): void
    {
        /** @var Ledger $ledger */
        $notOwnLedger = Ledger::factory()
            ->for(User::factory())
            ->for(Currency::first())
            ->create();

        $ownButDifferentLedger = Ledger::factory()
            ->for($this->user)
            ->for(Currency::first())
            ->create();

        /** @var Category $parentCategory */
        $parentCategory = Category::factory()
            ->for($notOwnLedger)
            ->expenseType()
            ->create();

        /** @var Category $anotherParentCategory */
        $anotherParentCategory = Category::factory()
            ->for($ownButDifferentLedger)
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'parent_id' => Hashids::encode($parentCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'parent_id' => Hashids::encode($anotherParentCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_asserts_parent_have_the_same_category_type()
    {
        /** @var Category $parentCategory */
        $parentCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'category_type' => CategoryTypeState::INCOME->value,
                'parent_id'     => Hashids::encode($parentCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_asserts_user_can_update_own_category(): void
    {
        $attributes = [
            'name'          => $this->faker->word,
            'notes'         => $this->faker->sentence,
            'category_type' => CategoryTypeState::INCOME->value,
            'order'         => 1
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'            => $this->category->id,
            'category_type' => CategoryTypeState::INCOME->value,
            'name'          => $attributes['name'],
            'notes'         => $attributes['notes'],
        ]);
    }

    public function test_assert_user_can_update_parent(): void
    {
        /** @var Category $parentCategory */
        $parentCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $attributes = [
            'name'          => $this->category->name,
            'category_type' => $this->category->category_type,
            'parent_id'     => Hashids::encode($parentCategory->id)
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'        => $this->category->id,
            'parent_id' => $parentCategory->id
        ]);
    }

    public function test_assert_category_return_correct_child(): void
    {
        $categoryIds = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->state([
                'parent_id' => $this->category->id
            ])
            ->count(2)
            ->create()
            ->map(fn (Category $category) => Hashids::encode($category->id))
            ->toArray();

        $response = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name'          => $this->faker->word,
                'category_type' => $this->category->category_type,
                'order'         => $this->category->order
            ])
            ->getContent();

        $responseChildIds = json_decode($response)->result->child;

        $this->assertEquals($categoryIds, $responseChildIds);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name'          => $this->faker->word,
                'category_type' => $this->category->category_type,
                'order'         => $this->category->order
            ])
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'parent_id',
                    'name',
                    'notes',
                    'order',
                    'category_type',
                    'is_visible',
                    'is_budgetable',
                    'is_reportable'
                ])
            );
    }
}
