<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;

/**
 * @property string $uuid
 */
trait UseUuid
{
	protected static function bootUseUuid(): void
	{
		static::creating(static function (self $model): void {
			$model->addUuid();
		});
	}

	protected function addUuid(): void
	{
		$this->uuid = $this->generateUuid();
	}

	/**
	 * @return UuidInterface
	 */
	protected function generateUuid(): string
	{
		return Str::orderedUuid();
	}

	/**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName(): string
	{
		return 'uuid';
	}
}
