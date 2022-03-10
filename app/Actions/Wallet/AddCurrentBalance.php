<?php

namespace App\Actions\Wallet;

use App\Models\Wallet;

class AddCurrentBalance
{
	public function __construct(
		protected Wallet $wallet,
		protected float $amount
	) {
	}

	public function execute(): void
	{
		$this->wallet->update([
			'current_balance' => $this->wallet->current_balance + $this->amount,
		]);
	}
}
