<?php

namespace App\DataObject;

use App\Enums\WalletTypeState;

class WalletData
{
	public function __construct(
		public string $name,
		public string $description,
		public float $current_balance,
		public WalletTypeState $wallet_type
	) {
	}
}
