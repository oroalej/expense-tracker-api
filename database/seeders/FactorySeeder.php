<?php

namespace Database\Seeders;

use App\Enums\CategoryTypeState;
use App\Enums\WalletAccessTypeState;
use App\Models\Category;
use App\Models\Scopes\UserAuthenticated;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
            ->count(2)
            ->has(
                Category::factory()
                    ->isDefault()
                    ->isNotEditable()
                    ->count(10)
                    ->state(
                        new Sequence(
                            [
                                'category_type' =>
                                    CategoryTypeState::Income->value,
                            ],
                            [
                                'category_type' =>
                                    CategoryTypeState::Expense->value,
                            ]
                        )
                    )
            )
            ->has(Tag::factory()->count(10))
            ->hasAttached(Wallet::factory()->count(4), [
                'access_type' => WalletAccessTypeState::Owner,
            ])
            ->create()
            ->each(static function (User $user) {
                Transaction::factory()
                    ->count(50)
                    ->for($user)
                    ->for(
                        $user
                            ->wallets()
                            ->withoutGlobalScope(UserAuthenticated::class)
                            ->inRandomOrder()
                            ->first()
                    )
                    ->for(
                        $user
                            ->categories()
                            ->withoutGlobalScope(UserAuthenticated::class)
                            ->inRandomOrder()
                            ->first()
                    )
                    ->hasAttached(
                        $user
                            ->tags()
                            ->withoutGlobalScope(UserAuthenticated::class)
                            ->inRandomOrder()
                            ->first()
                    )
                    ->create();
            });

        //            ->each(static function (User $user) {
        //                Transaction::factory()
        //                    ->for($user)
        //                    ->count(200)
        //                    ->for(
        //                        Category::factory()
        //                            ->count(20)
        //                            ->state(
        //                                new Sequence(
        //                                    [
        //                                        'category_type' =>
        //                                            CategoryTypeState::Expense,
        //                                    ],
        //                                    [
        //                                        'category_type' =>
        //                                            CategoryTypeState::Income,
        //                                    ]
        //                                )
        //                            )
        //                    );
        //            });
    }
}
