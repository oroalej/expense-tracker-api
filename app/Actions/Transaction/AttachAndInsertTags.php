<?php

namespace App\Actions\Transaction;

use App\Actions\Tag\CreateTag;
use App\Models\Transaction;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class AttachAndInsertTags
{
	public function __construct(
		protected Transaction $transaction,
		protected array $tags
	) {
	}

	/**
	 * @throws Throwable
	 * @throws AuthenticationException
	 */
	public function execute(): void
	{
		foreach ($this->tags as $attributes) {
			if (empty($attributes['id'])) {
				(new CreateTag($attributes['name']))
					->setTransaction($this->transaction)
					->execute();
			} else {
				$this->transaction->tags()->attach($attributes['id']);
			}
		}
	}
}
