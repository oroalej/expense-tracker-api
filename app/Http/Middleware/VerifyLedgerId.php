<?php

namespace App\Http\Middleware;

use App\Models\Ledger;
use Auth;
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

        $ledgerIdHashId = Hashids::decode($request->header('X-LEDGER-ID', ''));

        if (empty($ledgerIdHashId)) {
            abort(404);
        }

        $ledger = Ledger::where('id', $ledgerIdHashId[0])
            ->where('user_id', Auth::id())
            ->firstOr(function () {
                abort(404);
            });

        $request->merge([
            'ledger' => $ledger,
        ]);

        return $next($request);
    }
}
