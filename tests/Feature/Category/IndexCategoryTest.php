<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Currency;
use App\Models\Ledger;
use Tests\TestCase;

class IndexCategoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/categories";
    }

    public function test_guest_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_correct_returned_data(): void
    {
        $ledger = Ledger::factory()
            ->for($this->user)
            ->for(Currency::first())
            ->create();

        Category::factory()
            ->for($ledger)
            ->for(CategoryGroup::factory()->for($ledger))
            ->create();

        $categoryIds = Category::withoutGlobalScopes()
            ->select([
                'id', 'ledger_id', 'name', 'notes', 'order', 'category_group_id'
            ])
            ->where('ledger_id', $this->ledger->id)
            ->withCount('transactions')
            ->orderBy('category_group_id')
            ->orderBy('order')
            ->pluck('id')
            ->toArray();

        $responseCategoryIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->getOriginalContent()['result']
            ->pluck('id')
            ->toArray();

        $this->assertEquals($categoryIds, $responseCategoryIds);
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
                    ])
                )
            );
    }
}
