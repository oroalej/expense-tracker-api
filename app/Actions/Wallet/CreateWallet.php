<?php

namespace App\Actions\Wallet;

use App\DataObject\WalletData;
use App\Enums\WalletAccessTypeState;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateWallet
{
	protected User|null $user = null;

	/**
	 * @throws AuthenticationException
	 */
	public function __construct(protected WalletData $attributes)
	{
		if (!auth()->check()) {
			throw new AuthenticationException();
		}

		$this->user = auth()->user();
	}

	public function setUser(User $user): CreateWallet
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @throws Throwable
	 */
	public function execute(): void
	{
		DB::transaction(function () {
			$wallet = Wallet::create([
				'name' => $this->attributes->name,
				'description' => $this->attributes->description,
				'current_balance' => $this->attributes->current_balance,
				'wallet_type' => $this->attributes->wallet_type->value,
			]);

			$wallet->users()->attach($this->user->id, [
				'access_type' => WalletAccessTypeState::Owner->value,
			]);
		});
	}
}
