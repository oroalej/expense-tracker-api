<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Ledger;
use App\Models\User;
use Tests\TestCase;

class ChangeCategoryGroupTest extends TestCase
{
    public string   $url;
    public Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for(CategoryGroup::factory()
                ->for($this->ledger))
            ->create();

        $this->url = "api/categories/{$this->category->uuid}/change-category-group";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_you_can_only_access_own_data(): void
    {
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_new_category_group_is_yours(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for(
                Ledger::factory()
                    ->for(User::factory())
            )
            ->create();

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'id' => $categoryGroup->uuid
            ])
            ->assertJsonValidationErrors('id');
    }

    public function test_assert_new_category_group_belongs_to_the_same_ledger(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for(
                Ledger::factory()
                    ->for($this->user)
            )
            ->create();

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'id' => $categoryGroup->uuid
            ])
            ->assertJsonValidationErrors('id');
    }

    public function test_assert_new_category_group_reflects_in_database(): void
    {
        /** @var CategoryGroup $categoryGroup */
        $categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url, [
                'id' => $categoryGroup->uuid
            ])
            ->assertOk();

        $this->category->refresh();

        $this->assertDatabaseHas('categories', [
            'id'                => $this->category->id,
            'category_group_id' => $this->category->category_group_id
        ]);
    }
}
