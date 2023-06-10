<?php

namespace App\Models\Traits;

use Ramsey\Uuid\UuidInterface;
use Str;

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

    protected static function findUuid(string $uuid)
    {
        return self::where('uuid', $uuid)->first();
    }

    protected static function findUuidOrFail(string $uuid)
    {
        return self::where('uuid', $uuid)->firstOrFail();
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
