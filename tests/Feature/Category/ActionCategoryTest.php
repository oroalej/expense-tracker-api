<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ActionCategoryTest extends TestCase
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

        $this->url = "api/categories/$categoryId/actions";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_you_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_is_visible_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_visible');
    }

    public function test_assert_is_budgetable_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_budgetable');
    }

    public function test_assert_is_reportable_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonMissingValidationErrors('is_reportable');
    }

    public function test_assert_is_visible_field_correct_format()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'is_visible' => 'test'
            ])
            ->assertJsonValidationErrors('is_visible');
    }

    public function test_assert_is_budgetable_field_correct_format()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'is_budgetable' => 'test'
            ])
            ->assertJsonValidationErrors('is_budgetable');
    }

    public function test_assert_is_reportable_field_correct_format()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'is_reportable' => 'test'
            ])
            ->assertJsonValidationErrors('is_reportable');
    }

    public function test_assert_category_can_be_hidden(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $categoryId = Hashids::encode($category->id);

        $this->assertDatabaseHas('categories', [
            'id'         => $category->id,
            'is_visible' => 1
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/categories/$categoryId/actions", [
                'is_visible' => false
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'         => $category->id,
            'is_visible' => 0
        ]);
    }

    public function test_assert_category_can_be_visible(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->visible(0)
            ->create();

        $categoryId = Hashids::encode($category->id);

        $this->assertDatabaseHas('categories', [
            'id'         => $category->id,
            'is_visible' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/categories/$categoryId/actions", [
                'is_visible' => true
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'         => $category->id,
            'is_visible' => 1
        ]);
    }

    public function test_assert_category_is_reportable(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->reportable(false)
            ->create();

        $categoryId = Hashids::encode($category->id);

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_reportable' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/categories/$categoryId/actions", [
                'is_reportable' => true
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_reportable' => 1
        ]);
    }

    public function test_assert_category_is_not_reportable(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $categoryId = Hashids::encode($category->id);

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_reportable' => 1
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/categories/$categoryId/actions", [
                'is_reportable' => false
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_reportable' => 0
        ]);
    }

    public function test_assert_category_is_budgetable(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->budgetable(0)
            ->create();

        $categoryId = Hashids::encode($category->id);

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_budgetable' => 0
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/categories/$categoryId/actions", [
                'is_budgetable' => true
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_budgetable' => 1
        ]);
    }

    public function test_assert_category_is_not_budgetable(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $categoryId = Hashids::encode($category->id);

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_budgetable' => 1
        ]);

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson("api/categories/$categoryId/actions", [
                'is_budgetable' => false
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'            => $category->id,
            'is_budgetable' => 0
        ]);
    }
}
