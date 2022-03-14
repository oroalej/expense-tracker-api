<?php

namespace App\Http\Controllers;

use App\Actions\Wallet\CreateWallet;
use App\Actions\Wallet\UpdateWallet;
use App\DataObject\WalletData;
use App\Enums\WalletTypeState;
use App\Http\Requests\StoreWalletRequest;
use App\Http\Requests\UpdateWalletRequest;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class WalletController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWalletRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(StoreWalletRequest $request): JsonResponse
    {
        $walletData = new WalletData(
            $request->name,
            $request->description,
            $request->current_balance,
            WalletTypeState::tryFrom($request->wallet_type)
        );

        (new CreateWallet($walletData, auth()->user()))->execute();

        return response()->json([], Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateWalletRequest $request
     * @param Wallet              $wallet
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(
        UpdateWalletRequest $request,
        Wallet $wallet
    ): JsonResponse {
        $walletData = new WalletData(
            $request->name,
            $request->description,
            $request->current_balance,
            WalletTypeState::tryFrom($request->wallet_type)
        );

        (new UpdateWallet($wallet, $walletData))->execute();

        return response()->json([], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Wallet $wallet
     * @return JsonResponse
     */
    public function destroy(Wallet $wallet): JsonResponse
    {
        $wallet->delete();

        return response()->json([], Response::HTTP_OK);
    }
}
