<?php

namespace Tests\Feature\Account;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\User;
use Tests\TestCase;

class ArchivedAccountTest extends TestCase
{
    public string $url;

    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $accountType = AccountType::first();

        $this->account = Account::factory()
            ->for($accountType)
            ->for($this->ledger)
            ->create();

        $this->url = "api/accounts/{$this->account->uuid}/archived";
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

    public function test_set_account_to_archived(): void
    {
        $this->actingAs($this->user)
            ->withHeaders(['X-LEDGER-ID' => $this->ledger->uuid])
            ->postJson($this->url)
            ->assertOk();

        $this->account->refresh();

        $this->assertNotNull($this->account->archived_at);
        $this->assertTrue($this->account->is_archived);
    }
}
