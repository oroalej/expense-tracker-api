<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Ledger;
use App\Models\User;
use Illuminate\Database\Seeder;

class FactorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory()
            ->has(
                Ledger::factory()
                    ->count(2)
                    ->has(
                        CategoryGroup::factory()
                            ->has(Category::factory()->count(5))
                            ->count(2)
                    )
            )
            ->create();
    }
}
