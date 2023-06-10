<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Policies\AccountPolicy;
use App\Policies\CategoryGroupPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\LedgerPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class      => CategoryPolicy::class,
        CategoryGroup::class => CategoryGroupPolicy::class,
        Transaction::class   => TransactionPolicy::class,
        Ledger::class        => LedgerPolicy::class,
        Account::class       => AccountPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
