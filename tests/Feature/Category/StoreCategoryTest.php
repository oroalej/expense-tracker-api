<?php

namespace Category;

use App\Enums\CategoryTypeState;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class StoreCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/categories";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_assert_name_field_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_asserts_name_field_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name' => Str::random(256),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_notes_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'notes' => Str::random(255),
            ])
            ->assertJsonMissingValidationErrors('notes');
    }

    public function test_asserts_notes_is_not_more_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'notes' => Str::random(256),
            ])
            ->assertJsonValidationErrors('notes');
    }

    public function test_asserts_category_type_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_type');
    }

    public function test_asserts_category_type_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_type' => 1234
            ])
            ->assertJsonValidationErrors('category_type');
    }

    public function test_parent_is_own_and_same_ledger(): void
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
            ->postJson($this->url, [
                'parent_id' => Hashids::encode($parentCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'parent_id' => Hashids::encode($anotherParentCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_parent_have_the_same_category_type()
    {
        /** @var Category $parentCategory */
        $parentCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_type' => CategoryTypeState::INCOME->value,
                'parent_id'     => Hashids::encode($parentCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_assert_parent_category_is_top_level(): void
    {
        $topLevelCategory = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        /** @var Category $nextLevelCategory */
        $nextLevelCategory = Category::factory()
            ->for($this->ledger)
            ->for($topLevelCategory, 'parent')
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_type' => CategoryTypeState::EXPENSE->value,
                'parent_id'     => Hashids::encode($nextLevelCategory->id)
            ])
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_asserts_user_can_create_category(): void
    {
        $attributes = [
            'name'          => $this->faker->word,
            'notes'         => $this->faker->sentence,
            'category_type' => CategoryTypeState::INCOME->value
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('categories', [
            'name'          => $attributes['name'],
            'category_type' => CategoryTypeState::INCOME->value,
        ]);
    }

    public function test_asserts_user_can_create_category_with_parent(): void
    {
        /** @var Category $parentCategory */
        $parentCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $attributes = [
            'name'          => $this->faker->word,
            'notes'         => $this->faker->sentence,
            'category_type' => CategoryTypeState::INCOME->value,
            'parent_id'     => Hashids::encode($parentCategory->id)
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('categories', [
            'name'          => $attributes['name'],
            'category_type' => CategoryTypeState::INCOME->value,
            'parent_id'     => $parentCategory->id
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'name'          => $this->faker->word,
                'notes'         => $this->faker->sentence,
                'category_type' => CategoryTypeState::INCOME->value,
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
                    'is_reportable',
                    'child'
                ])
            );
    }
}
