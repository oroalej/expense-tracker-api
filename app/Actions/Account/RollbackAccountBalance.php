<?php

namespace App\Actions\Account;

use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Throwable;

class RollbackAccountBalance
{
    public function __construct(
        public Account $account,
        public ?float $inflow,
        public ?float $outflow
    ) {
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        DB::transaction(function () {
            if ($this->outflow) {
                (new AddCurrentBalanceAction())->execute(
                    $this->account,
                    $this->outflow
                );
            }

            if ($this->inflow) {
                (new DeductCurrentBalanceAction())->execute(
                    $this->account,
                    $this->inflow
                );
            }
        });
    }
}
