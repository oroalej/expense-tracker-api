<?php

namespace App\Actions\Transaction;

use App\Actions\Wallet\UpdateCurrentBalance;
use App\DataObject\TransactionData;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTransaction
{
	protected User|null $user;

	/**
	 * @throws AuthenticationException
	 */
	public function __construct(protected TransactionData $attributes)
	{
		if (!auth()->check()) {
			throw new AuthenticationException();
		}

		$this->user = auth()->user();
	}

	/**
	 * @throws Throwable
	 */
	public function execute(): Transaction
	{
		return DB::transaction(function () {
			$transaction = new Transaction([
				'amount' => $this->attributes->amount,
				'remarks' => $this->attributes->remarks,
				'transaction_date' => $this->attributes->transaction_date,
			]);

			$transaction->wallet()->associate($this->attributes->wallet_id);
			$transaction->category()->associate($this->attributes->category_id);
			$transaction->user()->associate($this->user);

			if ($this->attributes->parent_id) {
				$transaction->parent()->associate($this->attributes->parent_id);
			}

			$transaction->save();

			if (count($this->attributes->tags)) {
				$transaction->tags()->sync($this->attributes->tags);
			}

			(new UpdateCurrentBalance(
				$transaction->category->category_type,
				$transaction->wallet,
				$transaction->amount
			))->execute();

			// Create transaction's distributions if there's any.

			return $transaction;
		});
	}

	public function setUser(User $user): CreateTransaction
	{
		$this->user = $user;

		return $this;
	}
}
