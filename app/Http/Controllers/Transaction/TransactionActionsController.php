<?php

namespace App\Http\Controllers\Transaction;

use App\Actions\Transaction\ApproveTransactionAction;
use App\Actions\Transaction\ClearedTransactionAction;
use App\Actions\Transaction\RejectTransaction;
use App\Actions\Transaction\UnclearedTransaction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Throwable;

class TransactionActionsController extends Controller
{
    /**
     * @param  Transaction  $transaction
     * @param  ApproveTransactionAction  $approveTransaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function approved(Transaction $transaction, ApproveTransactionAction $approveTransaction): JsonResponse
    {
        $transaction = $approveTransaction->execute($transaction);

        return response()->json(new TransactionResource($transaction));
    }

    /**
     * @param  Transaction  $transaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function rejected(Transaction $transaction): JsonResponse
    {
        (new RejectTransaction($transaction))->execute();

        return response()->json(
            new TransactionResource($transaction->refresh())
        );
    }

    /**
     * @param  Transaction  $transaction
     * @param  ClearedTransactionAction  $clearedTransaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function cleared(Transaction $transaction, ClearedTransactionAction $clearedTransaction): JsonResponse
    {
        $clearedTransaction->execute($transaction);

        return response()->json(
            new TransactionResource($transaction->refresh())
        );
    }

    /**
     * @param  Transaction  $transaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function uncleared(Transaction $transaction): JsonResponse
    {
        (new UnclearedTransaction($transaction))->execute();

        return response()->json(
            new TransactionResource($transaction->refresh())
        );
    }
}
