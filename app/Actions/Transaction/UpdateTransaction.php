<?php

namespace App\Actions\Transaction;

use App\Actions\Wallet\RollbackCurrentBalance;
use App\Actions\Wallet\UpdateCurrentBalance;
use App\DataObject\TransactionData;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateTransaction
{
	public function __construct(
		protected Transaction $transaction,
		protected TransactionData $attributes
	) {
	}

	/**
	 * @throws Throwable
	 */
	public function execute(): Transaction
	{
		return DB::transaction(function () {
			$originalTransaction = $this->transaction->replicate(['id']);

			$this->transaction->fill([
				'remarks' => $this->attributes->remarks,
				'amount' => $this->attributes->amount,
				'transaction' => $this->attributes->transaction_date,
			]);

			$this->transaction
				->wallet()
				->associate($this->attributes->wallet_id);
			$this->transaction
				->category()
				->associate($this->attributes->category_id);

			if (count($this->attributes->tags)) {
				$this->transaction->tags()->sync($this->attributes->tags);
			}

			$this->transaction->save();

			$oldCategoryType = $originalTransaction->category->category_type;
			$newCategoryType = $this->transaction->category->category_type;

			if (
				$this->transaction->wasChanged(['wallet_id', 'amount']) ||
				($this->transaction->wasChanged('category_id') &&
					$oldCategoryType->value !== $newCategoryType->value)
			) {
				(new RollbackCurrentBalance(
					$originalTransaction->category->category_type,
					$originalTransaction->wallet,
					$originalTransaction->amount
				))->execute();

				(new UpdateCurrentBalance(
					$this->transaction->category->category_type,
					$this->transaction->wallet,
					$this->attributes->amount
				))->execute();
			}

			return $this->transaction->refresh();
		});
	}
}
