<?php

namespace Database\Seeders;

use App\Enums\TaxonomyState;
use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->data() as $attributes) {
            Taxonomy::create($attributes);
        }
    }

    public function data(): array
    {
        return [
            [
                'id'   => TaxonomyState::CategoryTypes->value,
                'name' => 'Category Types',
            ],
            [
                'id'   => TaxonomyState::AccountGroupTypes->value,
                'name' => 'Account Group Types',
            ]
        ];
    }
}
