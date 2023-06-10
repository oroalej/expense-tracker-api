<?php

namespace Tests\Feature\Ledger;

use App\Enums\DateFormatState;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Str;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class UpdateLedgerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $ledgerId  = Hashids::encode($this->ledger->id);
        $this->url = "api/ledgers/$ledgerId";
    }

    public function test_guest_not_allowed(): void
    {
        $this->putJson($this->url)->assertUnauthorized();
    }

    public function test_a_user_can_only_access_own_data(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->actingAs($anotherUser)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertNotFound();
    }

    public function test_assert_name_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_name_has_255_characters_max_length(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength),
            ])
            ->assertJsonMissingValidationErrors('name');
    }

    public function test_assert_name_is_not_longer_than_255_characters(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name' => Str::random(Builder::$defaultStringLength + 1),
            ])
            ->assertJsonValidationErrors('name');
    }

    public function test_assert_date_format_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('date_format');
    }

    public function test_assert_date_format_only_accept_valid_data(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'date_format' => "MM DD YYYY"
            ])
            ->assertJsonValidationErrors('date_format');
    }

    public function test_assert_currency_is_required(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url)
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_assert_currency_only_accept_valid_value(): void
    {
        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'currency_id' => Str::random()
            ])
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_user_can_update_own_ledger(): void
    {
        $currency   = Currency::first();
        $attributes = [
            'name'        => $this->faker->word,
            'date_format' => DateFormatState::MMDDYYYY_Slash->value,
            'currency_id' => Hashids::encode($currency->id)
        ];

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, $attributes)
            ->assertOk();

        $this->assertDatabaseHas('ledgers', [
            'id'          => $this->ledger->id,
            'name'        => $attributes['name'],
            'date_format' => $attributes['date_format'],
            'currency_id' => $currency->id
        ]);
    }

    public function test_assert_api_has_correct_structure(): void
    {
        $currency = Currency::first();

        $this->actingAs($this->user)
            ->appendHeaderLedgerId()
            ->putJson($this->url, [
                'name'        => $this->faker->word,
                'date_format' => DateFormatState::MMDDYYYY_Slash->value,
                'currency_id' => Hashids::encode($currency->id)
            ])
            ->assertJsonStructure(
                $this->apiStructure([
                    'id',
                    'name',
                    'is_archived',
                    'date_format',
                    'number_format' => [
                        'name',
                        'abbr',
                        'code',
                        'locale'
                    ],
                    'created_at',
                    'updated_at',
                    'archived_at',
                    'deleted_at',
                ])
            );
    }
}
