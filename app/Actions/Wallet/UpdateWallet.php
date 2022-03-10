<?php

namespace App\Actions\Wallet;

use App\DataObject\WalletData;
use App\Models\Wallet;
use Illuminate\Auth\AuthenticationException;

class UpdateWallet
{
	/**
	 * @throws AuthenticationException
	 */
	public function __construct(
		protected Wallet $wallet,
		protected WalletData $attributes
	) {
		if (!auth()->check()) {
			throw new AuthenticationException();
		}
	}

	public function execute(): Wallet
	{
		$this->wallet->update([
			'name' => $this->attributes->name,
			'description' => $this->attributes->description,
			'current_balance' => $this->attributes->current_balance,
			'wallet_type' => $this->attributes->wallet_type->value,
		]);

		// Check if there are changes in access.
		// Send email confirmation for new access.

		return $this->wallet->refresh();
	}
}
