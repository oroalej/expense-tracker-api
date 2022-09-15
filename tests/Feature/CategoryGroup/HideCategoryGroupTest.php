<?php

namespace Tests\Feature\CategoryGroup;

use App\Models\CategoryGroup;
use App\Models\User;
use Tests\TestCase;

class HideCategoryGroupTest extends TestCase
{
    public string $url;

    public CategoryGroup $categoryGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryGroup = CategoryGroup::factory()
            ->for($this->ledger)
            ->create();

        $this->url = "api/category-groups/{$this->categoryGroup->uuid}/hide";
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

    public function test_set_category_group_to_hidden(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertOk();

        $this->categoryGroup->refresh();

        $this->assertTrue($this->categoryGroup->is_hidden);
    }
}
