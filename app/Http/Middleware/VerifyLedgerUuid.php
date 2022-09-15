<?php

namespace App\Http\Middleware;

use App\Models\Ledger;
use Closure;
use Illuminate\Http\Request;

class VerifyLedgerUuid
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (
            ! $request->headers->has('X-LEDGER-ID') ||
            $request->header('X-LEDGER-ID') === ''
        ) {
            abort(404);
        }

        $ledger = Ledger::findUuid($request->header('X-LEDGER-ID'));

        if ($ledger === null) {
            abort(404);
        }

        $request->request->add([
            'ledger' => $ledger,
        ]);

        return $next($request);
    }
}
