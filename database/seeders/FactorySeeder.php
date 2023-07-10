<?php

namespace Database\Seeders;

use App\Enums\AccountTypeState;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class FactorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     * @throws Throwable
     */
    public function run(): void
    {
        $accountTypeId = AccountType::inRandomOrder()
            ->first()
            ->value('id');

        DB::transaction(static function () use ($accountTypeId) {
            User::factory()
                ->state(new Sequence(
                    [
                        'name'     => 'Test User',
                        'email'    => 'test@example.com',
                        'password' => Hash::make('1234')
                    ],
                    [
                        'name'  => 'Another Test User',
                        'email' => 'test1@example.com'
                    ],
                ))
                ->has(
                    Ledger::factory()
                        ->for(Currency::first())
                        ->has(
                            Account::factory()
                                ->count(3)
                                ->state(
                                    new Sequence(
                                        ['account_type_id' => AccountTypeState::Cash->value],
                                        ['account_type_id' => $accountTypeId],
                                    )
                                )
                        )
                        ->count(3)
                )
                ->count(2)
                ->create()
                ->each(static function (User $user) {
                    foreach ($user->ledgers as $ledger) {
                        $accountIds = $ledger->accounts()
                            ->pluck('id')
                            ->toArray();

                        $ledger->categories()
                            ->inRandomOrder()
                            ->limit(15)
                            ->get()
                            ->each(function (Category $category) use ($ledger, $accountIds) {
                                Transaction::factory()
                                    ->for($ledger)
                                    ->for($category)
                                    ->state(new Sequence(
                                        ['account_id' => $accountIds[0]],
                                        ['account_id' => $accountIds[1]],
                                        ['account_id' => $accountIds[2]],
                                    ))
                                    ->state(new Sequence(
                                        [
                                            'is_approved' => true,
                                            'is_cleared'  => true,
                                            'cleared_at'  => Carbon::now(),
                                            'approved_at' => Carbon::now()
                                        ],
                                        [
                                            'is_approved' => true,
                                            'approved_at' => Carbon::now(),
                                            'is_cleared'  => false,
                                            'cleared_at'  => null
                                        ],
                                    ))
                                    ->count(25)
                                    ->create();
                            });
                    }
                });
        });
    }
}
