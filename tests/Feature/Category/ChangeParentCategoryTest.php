<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ChangeParentCategoryTest extends TestCase
{
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $this->category = Category::factory()
            ->for($this->ledger)
            ->for($category, 'parent')
            ->incomeType()
            ->create();

        $categoryId = Hashids::encode($this->category->id);

        $this->url = "api/categories/$categoryId/change-parent-category";
    }

    public function test_guest_now_allowed(): void
    {
        $this->postJson($this->url)
            ->assertUnauthorized();
    }

    public function test_a_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()
            ->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_category_id_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_id');
    }

    public function test_assert_new_parent_category_type_same_with_child(): void
    {
        /** @var Category $category */
        $category = Category::factory()
            ->for($this->ledger)
            ->expenseType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode($category->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_assert_new_parent_category_is_top_level(): void
    {
        /** @var Category $topLevelCategory */
        $topLevelCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        /** @var Category $anotherCategory */
        $anotherCategory = Category::factory()
            ->for($this->ledger)
            ->for($topLevelCategory, 'parent')
            ->incomeType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode($anotherCategory->id)
            ])
            ->assertJsonValidationErrors('category_id');
    }

    public function test_assert_new_category_reflected(): void
    {
        /** @var Category $parentCategory */
        $parentCategory = Category::factory()
            ->for($this->ledger)
            ->incomeType()
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_id' => Hashids::encode($parentCategory->id)
            ])
            ->assertOk();

        $this->assertDatabaseHas('categories', [
            'id'        => $this->category->id,
            'parent_id' => $parentCategory->id
        ]);
    }
}
