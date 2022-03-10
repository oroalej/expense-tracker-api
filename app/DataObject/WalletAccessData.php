<?php

namespace App\DataObject;

use App\Enums\WalletAccessTypeState;
use Carbon\Carbon;

class WalletAccessData
{
	public function __construct(
		public User $user,
		public WalletAccessTypeState $access_type,
		public Carbon $start_date,
		public Carbon $end_date
	) {
	}
}
