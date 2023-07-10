<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\Transaction\Context\ActionTransactionContext;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClearTransactionController extends Controller
{
    /**
     * @param  Transaction  $transaction
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        DB::beginTransaction();

        try {
            $transaction = (new ActionTransactionContext($transaction))->clear();

            DB::commit();

            return $this->apiResponse([
                'data' => new TransactionResource($transaction),
                'message' => 'Transaction cleared.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  Transaction  $transaction
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function destroy(
        Transaction $transaction,
    ): JsonResponse {
        $this->authorize('update', $transaction);

        DB::beginTransaction();

        try {
            $transaction = (new ActionTransactionContext($transaction))->unclear();

            DB::commit();

            return $this->apiResponse([
                'data' => new TransactionResource($transaction),
                'message' => 'Transaction cleared.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
