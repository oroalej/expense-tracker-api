<?php

namespace App\Actions\Tag;

use App\DataObject\TagData;
use App\Models\Tag;
use App\Models\Transaction;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTag
{
	protected Transaction|null $transaction = null;

	/**
	 * @throws AuthenticationException
	 */
	public function __construct(public TagData $attributes)
	{
		if (is_null($this->attributes->user_id) && !auth()->check()) {
			throw new AuthenticationException();
		}

		if (is_null($this->attributes->user_id)) {
			$this->attributes->user_id = auth()->id();
		}
	}

	/**
	 * @throws Throwable
	 */
	public function execute(): Tag
	{
		return DB::transaction(function () {
			$tag = new Tag([
				'name' => $this->attributes->name,
				'description' => $this->attributes->description,
			]);

			$tag->user()->associate($this->attributes->user_id);
			$tag->save();

			if ($this->transaction) {
				$tag->transactions()->attach($this->transaction->id);
			}

			return $tag;
		});
	}

	public function setTransaction(Transaction $transaction): CreateTag
	{
		$this->transaction = $transaction;

		return $this;
	}
}
