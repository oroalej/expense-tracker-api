<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnhideCategoryTest extends TestCase
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

        $this->url = "api/categories/$categoryId/unhide";
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

    public function test_set_category_group_to_hidden(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertOk();

        $this->category->refresh();

        $this->assertFalse($this->category->is_hidden);
    }
}
