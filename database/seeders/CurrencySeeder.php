<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Currency::create([
            'name' => 'Philippine Peso',
            'abbr' => '₱',
            'code' => 'PHP',
            'locale' => 'en-PH',
        ]);
    }
}
