<?php

namespace App\Http\Middleware;

use App\Models\Ledger;
use Auth;
use Cache;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vinkla\Hashids\Facades\Hashids;

class VerifyLedgerId
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return RedirectResponse|Response|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->headers->has('X-LEDGER-ID') || $request->header('X-LEDGER-ID') === '') {
            abort(404);
        }

        $ledgerId = $request->header('X-LEDGER-ID', '');

        if (Cache::has($ledgerId)) {
            $ledger = Cache::get($ledgerId);
        } else {
            $ledgerIdHashId = Hashids::decode($ledgerId);

            if (empty($ledgerIdHashId)) {
                abort(404);
            }

            $ledger = Ledger::select([
                'id', 'user_id', 'currency_id', 'is_archived'
            ])
                ->where('id', $ledgerIdHashId[0])
                ->where('user_id', Auth::id())
                ->firstOr(function () {
                    abort(404);
                });

            Cache::set($ledgerId, $ledger);
        }

        $request->merge([
            'ledger' => $ledger,
        ]);

        return $next($request);
    }
}
