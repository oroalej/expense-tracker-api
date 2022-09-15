<?php

namespace App\Http\Controllers\Transaction;

use App\Actions\Transaction\CreateTransactionAction;
use App\Actions\Transaction\DeleteTransaction;
use App\Actions\Transaction\UpdateTransactionAction;
use App\DataTransferObjects\TransactionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransactionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Account  $account
     * @param  StoreTransactionRequest  $request
     * @param  CreateTransactionAction  $createTransaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function store(
        Account $account,
        StoreTransactionRequest $request,
        CreateTransactionAction $createTransaction
    ): JsonResponse {
        $transaction = $createTransaction->execute(
            new TransactionData(
                inflow: $request->input('inflow'),
                outflow: $request->input('outflow'),
                remarks: $request->input('remarks'),
                transaction_date: $request->input('transaction_date'),
                category: Category::findUuid($request->input('category_id')),
                account: $account,
                is_approved: $request->input('is_approved', true),
                is_cleared: $request->input('is_cleared', true),
                is_excluded: $request->input('is_excluded', false)
            )
        );

        return response()->json(
            new TransactionResource($transaction),
            Response::HTTP_CREATED
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Account  $account
     * @param  Transaction  $transaction
     * @param  UpdateTransactionRequest  $request
     * @param  UpdateTransactionAction  $updateTransaction
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function update(
        Account $account,
        Transaction $transaction,
        UpdateTransactionRequest $request,
        UpdateTransactionAction $updateTransaction
    ): JsonResponse {
        $transaction = $updateTransaction->execute(
            $transaction,
            new TransactionData(
                inflow: $request->input('inflow'),
                outflow: $request->input('outflow'),
                remarks: $request->input('remarks'),
                transaction_date: $request->input('transaction_date'),
                category: Category::findUuid($request->input('category_id')),
                account: $account,
                is_approved: $request->input('is_approved', true),
                is_cleared: $request->input('is_cleared', true),
                is_excluded: $request->input('is_excluded', false)
            )
        );

        return response()->json(
            new TransactionResource($transaction),
            Response::HTTP_OK
        );
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
        (new DeleteTransaction($transaction))->execute();

        return response()->json([], Response::HTTP_OK);
    }
}
