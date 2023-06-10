<?php

namespace Tests\Feature\Budget;

use Tests\TestCase;

class IndexBudgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->url = "api/budgets";
    }

    public function test_asserts_guest_not_allowed(): void
    {
        $this->getJson($this->url)
            ->assertUnauthorized();
    }

    public function test_assert_year_is_optional(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertOk();
    }

    public function test_assert_year_is_number(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url, ['year' => 'Hello World'])
            ->assertOk();
    }

    public function test_assert_year_is_valid(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, ['year' => 00])
            ->assertJsonValidationErrors('year');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, ['year' => 000])
            ->assertJsonValidationErrors('year');

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->json("GET", $this->url, ['year' => 20222])
            ->assertJsonValidationErrors('year');
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->getJson($this->url)
            ->assertJsonStructure(
                $this->apiStructure(
                    $this->apiStructureCollection([
                        'id',
                        'month',
                        'year',
                        'year_month'
                    ])
                )
            );
    }
}
