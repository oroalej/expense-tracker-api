<?php

namespace Database\Seeders;

use App\Enums\AccountGroupTypeState;
use App\Enums\TaxonomyState;
use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    public function run(): void
    {
        $this->insertData($this->getAccountTypes(), TaxonomyState::AccountGroupTypes);
    }

    public function insertData(array $data, TaxonomyState $taxonomy): void
    {
        foreach ($data as $item) {
            if (is_array($item)) {
                $item['taxonomy_id'] = $taxonomy->value;
            } else {
                $item = [
                    'name'        => $item,
                    'taxonomy_id' => $taxonomy->value,
                ];
            }

            Term::create($item);
        }
    }

    public function getAccountTypes(): array
    {
        return [
            [
                'id'   => AccountGroupTypeState::Budget->value,
                'name' => 'Budget'
            ],
            [
                'id'   => AccountGroupTypeState::Debt->value,
                'name' => 'Mortgages, Loans, And Debt'
            ],
            [
                'id'   => AccountGroupTypeState::Tracking->value,
                'name' => 'Tracking'
            ],
        ];
    }
}
