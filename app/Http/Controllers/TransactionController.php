<?php

namespace App\Http\Controllers;

use App\Actions\Transaction\CreateTransaction;
use App\Actions\Transaction\DeleteTransaction;
use App\Actions\Transaction\UpdateTransaction;
use App\DataObject\TransactionData;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransactionController extends Controller
{
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StoreTransactionRequest $request
	 * @return JsonResponse
	 * @throws Throwable
	 */
	public function store(StoreTransactionRequest $request): JsonResponse
	{
		$transactionData = new TransactionData(
			$request->amount,
			$request->remarks,
			$request->transaction_date,
			$request->category_id,
			$request->wallet_id,
			$request->tags ?? []
		);

		(new CreateTransaction($transactionData))->execute();

		return response()->json([], Response::HTTP_CREATED);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param UpdateTransactionRequest $request
	 * @param Transaction              $transaction
	 * @return JsonResponse
	 * @throws Throwable
	 */
	public function update(
		UpdateTransactionRequest $request,
		Transaction $transaction
	): JsonResponse {
		$transactionData = new TransactionData(
			$request->amount,
			$request->remarks,
			$request->transaction_date,
			$request->category_id,
			$request->wallet_id,
			$request->tags ?? []
		);

		(new UpdateTransaction($transaction, $transactionData))->execute();

		return response()->json([], Response::HTTP_OK);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Transaction $transaction
	 * @return JsonResponse
	 * @throws Throwable
	 */
	public function destroy(Transaction $transaction): JsonResponse
	{
		(new DeleteTransaction($transaction))->execute();

		return response()->json([], Response::HTTP_OK);
	}
}
