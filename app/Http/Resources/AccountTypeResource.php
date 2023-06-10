<?php

namespace App\Http\Resources;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property Collection $accounts
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AccountTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $accountIds = [];

        if ($this->accounts instanceof Collection) {
            $accountIds = $this->accounts->map(fn (Account $account) => Hashids::encode($account->id));
        }

        return [
            'id'          => Hashids::encode($this->id),
            'name'        => $this->name,
            'description' => $this->whenColumnLoaded('description'),
            'created_at'  => $this->whenColumnLoaded('created_at'),
            'updated_at'  => $this->whenColumnLoaded('updated_at'),
            $this->mergeWhen(count($accountIds), [
                'accounts' => $accountIds
            ])
        ];
    }
}
