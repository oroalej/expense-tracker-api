<?php

namespace Tests\Feature\CategoryGroup;

use App\Models\CategoryGroup;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnhideCategoryGroupTest extends TestCase
{
    public string $url;

    public CategoryGroup $categoryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $categoryGroupId = Hashids::encode($this->categoryGroup->id);

        $this->url = "api/category-groups/$categoryGroupId/unhide";
    }

    public function test_guest_not_allowed(): void
    {
        $this->postJson($this->url)->assertUnauthorized();
    }

    public function test_you_can_only_access_own_data(): void
    {
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

        $this->categoryGroup->refresh();

        $this->assertFalse($this->categoryGroup->is_hidden);
    }
}
