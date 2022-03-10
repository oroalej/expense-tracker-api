<?php

namespace App\DataObject;

class TransactionData
{
	public function __construct(
		public float $amount,
		public string $remarks,
		public string $transaction_date,
		public int $category_id,
		public int $wallet_id,
		public array $tags = [],
		public int|null $parent_id = null
	) {
	}
}
