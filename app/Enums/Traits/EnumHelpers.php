<?php

namespace App\Enums\Traits;

use Illuminate\Support\Arr;
use ReflectionEnum;

trait EnumHelpers
{
    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getValue($value): mixed
    {
        return Arr::get(self::cases(), $value);
    }

    public static function fromCase($case)
    {
        return (new ReflectionEnum(self::class))->getCase($case)->getValue()->value;
    }
}
