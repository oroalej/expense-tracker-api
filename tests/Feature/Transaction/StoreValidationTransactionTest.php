<?php

namespace Tests\Feature\Transaction;

use Tests\TestCase;

class StoreValidationTransactionTest extends TestCase
{
    public string $url = "api/transactions";

    public function test_inflow_and_outflow_only_accept_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['outflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('outflow');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->postJson($this->url, ['inflow' => 'Hello Code Reviewer :)'])
            ->assertJsonValidationErrors('inflow');
    }
}
