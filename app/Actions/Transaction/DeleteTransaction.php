<?php

namespace App\Actions\Transaction;

use App\Actions\Wallet\RollbackCurrentBalance;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteTransaction
{
	public function __construct(protected Transaction $transaction)
	{
	}

	/**
	 * @throws Throwable
	 */
	public function execute(): void
	{
		DB::transaction(function () {
			$this->transaction->delete();

			(new RollbackCurrentBalance(
				$this->transaction->category->category_type,
				$this->transaction->wallet,
				$this->transaction->amount
			))->execute();
		});
	}
}
