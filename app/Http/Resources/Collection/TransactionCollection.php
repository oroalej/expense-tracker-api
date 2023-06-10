<?php

namespace App\Http\Resources\Collection;

use App\Http\Resources\TransactionResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @mixin LengthAwarePaginator
 */
class TransactionCollection extends ResourceCollection
{
    public $collects = TransactionResource::class;

    public function toArray($request)
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'data' => $this->collection,
                'meta' => [
                    'current_page' => $this->currentPage(),
                    'per_page'     => $this->perPage(),
                    'from'         => $this->firstItem(),
                    'last_page'    => $this->lastPage(),
                    'to'           => $this->lastItem(),
                    'total'        => $this->total(),
                ],
            ];
        }

        return $this->collection;
    }
}
