<?php

namespace App\Actions\Account;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdjustAccountBalanceAction
{
    public function __construct(
        public Account $account,
        protected readonly ?float $inflow,
        protected readonly ?float $outflow
    ) {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        DB::transaction(function () {
            if ($this->inflow) {
                (new AddCurrentBalanceAction())->execute(
                    $this->account->refresh(),
                    $this->inflow
                );
            }

            if ($this->outflow) {
                (new DeductCurrentBalanceAction())->execute(
                    $this->account->refresh(),
                    $this->outflow
                );
            }
        });
    }
}
