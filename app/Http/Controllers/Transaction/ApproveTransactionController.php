<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ApproveTransactionController extends Controller
{
    /**
     * @param  Transaction  $transaction
     * @return JsonResponse
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function store(Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        DB::beginTransaction();

        try {
            $transaction = (new TransactionService())->approve($transaction);

            DB::commit();

            return $this->apiResponse([
                'data' => new TransactionResource($transaction),
                'message' => 'Transaction approved.'
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
            $transaction = (new TransactionService())->reject($transaction);

            DB::commit();

            return $this->apiResponse([
                'data' => new TransactionResource($transaction),
                'message' => 'Transaction rejected.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
