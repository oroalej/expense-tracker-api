<?php

namespace App\Http\Controllers\Account;

use App\DTO\AccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreAccountRequest;
use App\Http\Requests\Update\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Services\AccountService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $accounts = Account::select([
            'id',
            'ledger_id',
            'account_type_id',
            'name',
            'current_balance',
            'is_archived'
        ])
            ->where('ledger_id', $request->ledger->id)
            ->get();

        return $this->apiResponse([
            'data' => AccountResource::collection($accounts),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAccountRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $account = (new AccountService())->store(
                AccountData::fromRequest($request)
            );

            DB::commit();

            return $this->apiResponse([
                'data' => new AccountResource($account),
                'message' => 'Account successfully created.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Account  $account
     * @return JsonResponse
     */
    public function show(Account $account): JsonResponse
    {
        return $this->apiResponse([
            'data' => new AccountResource($account),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAccountRequest  $request
     * @param  Account  $account
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        UpdateAccountRequest $request,
        Account $account,
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $account = (new AccountService())->update(
                $account,
                new AccountData(
                    name: $request->validated('name'),
                    current_balance: $request->validated('current_balance'),
                    account_type: $account->accountType,
                    ledger: $request->ledger
                )
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new AccountResource($account),
                'message' => 'Successfully Updated',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Account  $account
     * @return JsonResponse
     */
    public function destroy(Account $account): JsonResponse
    {
        return $this->apiResponse([
            'message' => 'Successfully Updated',
        ]);
    }
}
