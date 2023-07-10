<?php

namespace App\Http\Controllers\Transaction;

use App\DTO\TransactionData;
use App\Enums\OperationState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Index\IndexTransactionRequest;
use App\Http\Requests\Store\StoreTransactionRequest;
use App\Http\Requests\Update\UpdateTransactionRequest;
use App\Http\Resources\Collection\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\Transaction\Context\ActionTransactionContext;
use App\Services\Transaction\Context\StoreTransactionContext;
use App\Services\Transaction\Context\UpdateTransactionContext;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Vinkla\Hashids\Facades\Hashids;

class TransactionController extends Controller
{
    public function index(IndexTransactionRequest $request): JsonResponse
    {
//        $summary = Transaction::getBalanceSummary(
//            $request->validated('account_id'),
//            $request->validated('category_id')
//        );

        $transactions = Transaction::defaultSelect()
            ->where('ledger_id', $request->ledger->id)
            ->where('account_id', $request->validated('account_id'))
            ->when($request->filled('categories'), static function (Builder $builder) use ($request) {
                // EXAMPLE: excluded:295E4Lrl6Rqm,aKyml8dlM3pW
                [$type, $ids] = explode(':', $request->get('categories'));
                $encodedIds = explode(',', $ids);
                $ids        = [];

                foreach ($encodedIds as $id) {
                    $decoded = Hashids::decode($id);

                    if (count($decoded)) {
                        $ids[] = $decoded[0];
                    }
                }

                if ($type === 'included') {
                    $builder->whereIn('category_id', $ids);
                } else {
                    $builder->whereNotIn('category_id', $ids);
                }
            })
            ->when($request->filled('state'), static function (Builder $builder) use ($request) {
                switch ($request->validated('state')) {
                    case 'action':
                        $builder->where('is_approved', 0);
                        break;
                    case 'clear':
                        $builder->where('is_cleared', 0)
                            ->where('is_approved', 1);
                        break;
                }
            })
            ->when($request->filled('amount'), static function (Builder $builder) use ($request) {
                // EXAMPLE: BETWEEN,1,2
                // [$operation, $from, $to]
                $data = explode(',', $request->get('amount'));

                switch ($data[0]) {
                    case 'BETWEEN':
                        $builder->whereBetween('amount', [$data[1], $data[2]]);
                        break;
                    default:
                        $builder->where('amount', OperationState::fromCase($data[0]), $data[1]);
                }
            })
            ->orderBy('is_approved')
            ->orderBy('is_cleared')
            ->when($request->filled('sort'), static function (Builder $builder) use ($request) {
                // EXAMPLE: category_id:desc,transaction_date:asc
                $sortList = explode(',', $request->get('sort'));

                foreach ($sortList as $sort) {
                    [$column, $order] = explode(':', $sort);
                    $builder->orderBy($column, $order);
                }
            }, static function (Builder $builder) {
                $builder->orderBy('transaction_date');
            })
            ->orderBy('created_at')
            ->paginate($request->input('limit', 50));

//        $withActionsCount = Transaction::where('ledger_id', $request->ledger->id)
//            ->where('account_id', $request->get('account_id'))
//            ->where('is_approved', 0)
//            ->count();
//
//        $withClearableCount = Transaction::where('ledger_id', $request->ledger->id)
//            ->where('account_id', $request->get('account_id'))
//            ->where('is_cleared', 0)
//            ->where('is_approved', 1)
//            ->count();

        return $this->apiResponse([
            'data' => [
//                'summary'        => $summary,
//                'with_actions'   => $withActionsCount,
//                'with_clearable' => $withClearableCount,
                'paginated' => new TransactionCollection($transactions)
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
            $transaction = (new StoreTransactionContext(
                TransactionData::fromRequest($request)
            ))->execute();

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
            $transaction = (new UpdateTransactionContext(
                $transaction,
                TransactionData::fromRequest($request)
            ))->execute();

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
            (new ActionTransactionContext($transaction))->delete();

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
