<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Index\IndexTransactionRequest;
use App\Http\Resources\Collection\TransactionCollection;
use App\Models\Account;
use App\Models\Transaction;

class AccountTransactionController extends Controller
{
    public function __invoke(Account $account, IndexTransactionRequest $request)
    {
        $summary = Transaction::getBalanceSummary(
            (int) $request->get('account_id'),
            (int) $request->get('category_id')
        );

        $transactions = $account->transactions()
            ->defaultSelect()
            ->where('ledger_id', $request->ledger->id)
            ->orderBy('is_cleared')
            ->orderBy('is_approved')
            ->orderBy($request->input('sort', 'transaction_date'), $request->input('order', 'desc'))
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return $this->apiResponse([
            'data' => [
                'summary'     => $summary,
                'paginated'   => new TransactionCollection($transactions),
                'account_id'  => $request->input('account_id'),
                'account_id1' => $request->get('account_id'),
                'account_id2' => $request->all(),
            ],
        ]);
    }
}
