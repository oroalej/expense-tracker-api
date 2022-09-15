<?php

namespace App\Http\Controllers\Account;

use App\Actions\Account\CreateAccountAction;
use App\Actions\Account\UpdateAccountAction;
use App\DataTransferObjects\AccountData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAccountRequest  $request
     * @param  CreateAccountAction  $createAccount
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(
        StoreAccountRequest $request,
        CreateAccountAction $createAccount
    ): JsonResponse {
        $account = $createAccount->execute(
            new AccountData(
                name: $request->input('name'),
                current_balance: $request->input('current_balance'),
                account_type: AccountType::findUuid($request->input('account_type_id')),
                ledger: $request->ledger
            )
        );

        return response()->json(
            new AccountResource($account),
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateAccountRequest  $request
     * @param  Account  $account
     * @param  UpdateAccountAction  $updateAccount
     * @return JsonResponse
     */
    public function update(
        UpdateAccountRequest $request,
        Account $account,
        UpdateAccountAction $updateAccount
    ): JsonResponse {
        $account = $updateAccount->execute(
            $account,
            new AccountData(
                name: $request->input('name'),
                current_balance: $request->input('current_balance'),
                account_type: $account->accountType,
                ledger: $request->ledger
            )
        );

        return response()->json(new AccountResource($account));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
    }
}
