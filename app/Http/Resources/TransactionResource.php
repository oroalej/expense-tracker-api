<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property-read int $category_id
 * @property-read int $account_id
 * @property-read int $ledger_id
 * @property-read float $inflow
 * @property-read float $outflow
 * @property-read string $remarks
 * @property-read bool $is_approved
 * @property-read bool $is_cleared
 * @property-read Carbon $transaction_date
 * @property-read Carbon|null $approved_at
 * @property-read Carbon|null $cleared_at
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'               => Hashids::encode($this->id),
            'inflow'           => $this->inflow,
            'outflow'          => $this->outflow,
            'remarks'          => $this->remarks,
            'transaction_date' => $this->transaction_date->format('Y-m-d'),
            'is_approved'      => $this->whenColumnLoaded('is_approved'),
            'is_cleared'       => $this->whenColumnLoaded('is_cleared'),
            'approved_at'      => $this->whenColumnLoaded('approved_at'),
            'cleared_at'       => $this->whenColumnLoaded('cleared_at'),
            'rejected_at'      => $this->whenColumnLoaded('rejected_at'),
            'created_at'       => $this->whenColumnLoaded('created_at'),
            'updated_at'       => $this->whenColumnLoaded('updated_at'),
            'deleted_at'       => $this->whenColumnLoaded('deleted_at'),
            $this->mergeWhen($this->category_id, [
                'category_id' => Hashids::encode($this->category_id)
            ]),
            $this->mergeWhen($this->account_id, [
                'account_id' => Hashids::encode($this->account_id)
            ]),
            $this->mergeWhen($this->ledger_id, [
                'ledger_id' => Hashids::encode($this->ledger_id)
            ]),
        ];
    }
}
