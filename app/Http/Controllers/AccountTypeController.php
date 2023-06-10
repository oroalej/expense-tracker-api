<?php

namespace App\Http\Controllers;

use App\Enums\TaxonomyState;
use App\Http\Resources\AccountTypeResource;
use App\Http\Resources\TermResource;
use App\Models\AccountType;
use App\Models\Term;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $terms = Term::select(['id', 'name'])
            ->where('taxonomy_id', TaxonomyState::AccountGroupTypes->value)
            ->with('accountTypes:id,group_type_id,name')
            ->orderBy('id')
            ->get();

        $accountTypes = AccountType::with([
            'accounts' => static function (HasMany $builder) use ($request) {
                $builder->select([
                    'accounts.id',
                    'account_type_id'
                ])
                    ->where('ledger_id', $request->ledger->id);
            }
        ])
            ->has('accounts')
            ->select(['id', 'name'])
            ->get();

        return $this->apiResponse([
            'data' => [
                'account_type_grouping' => TermResource::collection($terms),
                'account_types'         => AccountTypeResource::collection($accountTypes)
            ]
        ]);
    }
}
