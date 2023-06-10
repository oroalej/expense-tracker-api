<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $name
 * @property-read string $abbr
 * @property-read string $code
 * @property-read string $locale
 */
class CurrencyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name'   => $this->name,
            'abbr'   => $this->abbr,
            'code'   => $this->code,
            'locale' => $this->locale,
        ];
    }
}
