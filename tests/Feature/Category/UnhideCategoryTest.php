<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\User;
use Tests\TestCase;

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
            ->create();

        $this->url = "api/categories/{$this->category->uuid}/unhide";
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

        $this->category->refresh();

        $this->assertFalse($this->category->is_hidden);
    }
}
