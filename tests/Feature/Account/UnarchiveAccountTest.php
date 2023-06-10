<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\User;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UnarchiveAccountTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $accountType = AccountType::first();

        $this->account = Account::factory()
            ->for($accountType)
            ->for($this->ledger)
            ->create();

        $accountId = Hashids::encode($this->account->id);

        $this->url = "api/accounts/$accountId/unarchive";
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

    public function test_set_account_to_archived(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url)
            ->assertOk();

        $this->account->refresh();

        $this->assertNull($this->account->archived_at);
        $this->assertFalse($this->account->is_archived);
    }
}
