<?php

namespace Tests\Feature\Ledger;

use App\Models\Currency;
use App\Models\Ledger;
use App\Models\User;
use Tests\TestCase;

class IndexLedgerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/ledgers";
    }

    public function test_guest_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_correct_returned_data(): void
    {
        Ledger::factory()
            ->for(User::factory())
            ->for(Currency::first());

        $ledgers = $this->user
            ->ledgers()
            ->pluck('id')
            ->toArray();

        $responseLedgerIds = $this->actingAs($this->user)
            ->getJson($this->url)
            ->getOriginalContent()['result']
            ->pluck('id')
            ->toArray();

        $this->assertEquals($ledgers, $responseLedgerIds);
    }

    public function test_api_has_correct_structure()
    {
        $this->actingAs($this->user)
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure(
                    $this->apiStructureCollection([
                        'id',
                        'number_format',
                        'name',
                        'date_format',
                        'is_archived',
                        'created_at',
                        'archived_at',
                        'updated_at',
                        'deleted_at',
                    ])
                )
            );
    }
}
