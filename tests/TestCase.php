<?php

namespace Tests;

use App\Models\Currency;
use App\Models\Ledger;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Vinkla\Hashids\Facades\Hashids;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use WithFaker;

    public User $user;

    public Ledger $ledger;

    public string $url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->user   = User::factory()->create();
        $this->ledger = Ledger::factory()
            ->for($this->user)
            ->for(Currency::first())
            ->create();
    }

    public function apiStructure(array $structure): array
    {
        return [
            'success',
            'message',
            'result' => $structure,
        ];
    }

    public function appendHeaderLedgerId(): self
    {
        $this->withHeader('X-LEDGER-ID', Hashids::encode($this->ledger->id));

        return $this;
    }

    public function apiStructureCollection(array $structure): array
    {
        return [
            '*' => $structure
        ];
    }

    public function apiStructurePaginated(array $structure): array
    {
        return [
            'data' => $this->apiStructureCollection($structure),
            'meta' => [
                'current_page',
                'per_page',
                'from',
                'last_page',
                'to',
                'total'
            ]
        ];
    }
}
