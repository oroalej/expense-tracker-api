<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Currency;
use App\Models\Ledger;
use Tests\TestCase;

class IndexAccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/accounts";
    }

    public function test_guest_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_correct_returned_data(): void
    {
        Account::factory()
            ->for(
                Ledger::factory()
                    ->for($this->user)
                    ->for(Currency::first())
            )
            ->for(AccountType::first())
            ->create();

        $accountIds = $this->ledger->accounts()
            ->pluck('id')
            ->toArray();

        $responseAccountIds = $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->getOriginalContent()['result']
            ->pluck('id')
            ->toArray();

        $this->assertEquals($accountIds, $responseAccountIds);
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
                        'account_type_id',
                        'ledger_id',
                        'name',
                        'current_balance',
                        'is_archived'
                    ])
                )
            );
    }
}
