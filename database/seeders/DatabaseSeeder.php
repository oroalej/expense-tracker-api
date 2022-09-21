<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            TaxonomySeeder::class,
            TermSeeder::class,
            AccountTypesSeeder::class
//            CurrencySeeder::class,
        ]);
    }
}
