<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class ChangeCategoryGroupTest extends TestCase
{
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for(
                CategoryGroup::factory()
                    ->for($this->ledger)
            )
            ->for($this->ledger)
            ->create();

        $categoryId = Hashids::encode($this->category->id);

        $this->url = "api/categories/$categoryId/change-category-group";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)
            ->assertUnauthorized();
    }

    public function test_a_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertNotFound();
    }

    public function test_category_group_id_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertJsonValidationErrors('category_group_id');
    }

    public function test_assert_new_category_group_is_yours(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for(
                Ledger::factory()
                    ->for(User::factory())
                    ->for(Currency::first())
            )
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_group_id' => Hashids::encode($categoryGroup->id),
            ])
            ->assertJsonValidationErrors('category_group_id');
    }

    public function test_assert_new_category_group_belongs_to_the_same_ledger(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for(
                Ledger::factory()
                    ->for($this->user)
                    ->for(Currency::first())
            )
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_group_id' => Hashids::encode($categoryGroup->id),
            ])
            ->assertJsonValidationErrors('category_group_id');
    }

    public function test_assert_new_category_group_reflects_in_database(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, [
                'category_group_id' => Hashids::encode($categoryGroup->id),
            ])
            ->assertOk();

        $this->category->refresh();

        $this->assertDatabaseHas('categories', [
            'id'                => $this->category->id,
            'category_group_id' => $this->category->category_group_id,
        ]);
    }
}
