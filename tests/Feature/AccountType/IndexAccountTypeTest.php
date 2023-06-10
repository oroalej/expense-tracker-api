<?php

namespace Tests\Feature\AccountType;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;

class IndexAccountTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/account-types";
    }

    public function test_guest_not_allowed(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_api_has_correct_structure()
    {
        $accountTypeIds = AccountType::inRandomOrder()
            ->limit(2)
            ->pluck('id')
            ->toArray();

        Account::factory()
            ->for($this->ledger)
            ->state(new Sequence(
                ['account_type_id' => $accountTypeIds[0]],
                ['account_type_id' => $accountTypeIds[1]],
            ))
            ->count(2)
            ->create();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk()
            ->assertJsonStructure(
                $this->apiStructure(
                    [
                    'account_type_grouping' => $this->apiStructureCollection([
                        'id',
                        'name',
                        'account_types' => $this->apiStructureCollection([
                            'id',
                            'name',
                        ])
                    ]),
                    'account_types'         => $this->apiStructureCollection([
                        'id',
                        'name',
                        'accounts'
                    ])
                ],
                )
            );
    }
}
