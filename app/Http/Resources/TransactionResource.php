<?php

namespace App\Http\Resources;

use App\Models\Account;
use App\Models\Category;
use Database\Factories\TransactionFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property-read int $id
 * @property-read int $account_id
 * @property-read int $category_id
 * @property-read float $inflow
 * @property-read float $outflow
 * @property-read string $remarks
 * @property-read Carbon $transaction_date
 * @property-read bool $is_approved
 * @property-read bool $is_cleared
 * @property-read Category $category
 * @property-read Account $account
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @method static TransactionFactory factory()
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
            'id' => $this->id,
            'inflow' => $this->inflow,
            'outflow' => $this->outflow,
            'remarks' => $this->remarks,
            'transaction_date' => $this->transaction_date,
            'is_approved' => $this->is_approved,
            'is_cleared' => $this->is_cleared,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
