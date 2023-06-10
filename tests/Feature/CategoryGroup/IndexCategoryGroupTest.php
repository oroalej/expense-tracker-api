<?php

namespace Tests\Feature\CategoryGroup;

use App\Models\CategoryGroup;
use App\Models\Currency;
use App\Models\Ledger;
use Tests\TestCase;

class IndexCategoryGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/category-groups";
    }

    public function test_guest_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_correct_returned_data(): void
    {
        CategoryGroup::factory()
            ->for(
                Ledger::factory()
                    ->for($this->user)
                    ->for(Currency::first())
            )
            ->create();

        $categoryGroupIds = $this->ledger
            ->categoryGroups()
            ->where('is_hidden', false)
            ->orderBy('category_groups.order')
            ->pluck('id')
            ->toArray();

        $responseCategoryIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->getOriginalContent()['result']
            ->pluck('id')
            ->toArray();

        $this->assertEquals($categoryGroupIds, $responseCategoryIds);
    }

    public function test_api_has_correct_structure()
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure(
                    $this->apiStructureCollection([
                        'id',
                        'name',
                        'notes',
                        'order',
                        'is_hidden',
                        'categories'
                    ])
                )
            );
    }
}
