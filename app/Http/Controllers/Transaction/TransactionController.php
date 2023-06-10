<?php

namespace App\Http\Controllers\Transaction;

use App\DTO\TransactionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Index\IndexTransactionRequest;
use App\Http\Requests\Store\StoreTransactionRequest;
use App\Http\Requests\Update\UpdateTransactionRequest;
use App\Http\Resources\Collection\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransactionController extends Controller
{
    public function index(IndexTransactionRequest $request): JsonResponse
    {
        $summary = Transaction::getBalanceSummary(
            $request->get('account_id'),
            $request->get('category_id')
        );

        $transactions = Transaction::getPaginatedData(
            accountId: $request->get('account_id'),
            categoryId: $request->get('category_id'),
            perPage: $request->input('per_page', 15)
        );

        return $this->apiResponse([
            'data' => [
                'summary'   => $summary,
                'paginated' => new TransactionCollection($transactions),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreTransactionRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $transaction = (new TransactionService())->store(
                TransactionData::fromRequest($request)
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new TransactionResource($transaction),
                'message' => 'Transaction successfully created.',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Transaction  $transaction
     * @param  UpdateTransactionRequest  $request
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function update(
        Transaction $transaction,
        UpdateTransactionRequest $request,
    ): JsonResponse {
        DB::beginTransaction();

        try {
            $transaction = (new TransactionService())->update(
                transaction: $transaction,
                attributes: TransactionData::fromRequest($request)
            );

            DB::commit();

            return $this->apiResponse([
                'data'    => new TransactionResource($transaction),
                'message' => 'Transaction successfully updated.',
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
     * @param  Transaction  $transaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        DB::beginTransaction();

        try {
            (new TransactionService())->delete($transaction);

            DB::commit();

            return $this->apiResponse([
                'message' => 'Transaction successfully deleted.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
