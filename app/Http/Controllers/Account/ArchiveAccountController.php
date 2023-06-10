<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\AccountService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ArchiveAccountController extends Controller
{
    /**
     * @param  Account  $account
     * @return void
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(Account $account): void
    {
        $this->authorize('update', $account);

        DB::beginTransaction();

        try {
            (new AccountService())->archive($account);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param  Account  $account
     * @return void
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function destroy(Account $account): void
    {
        $this->authorize('update', $account);

        DB::beginTransaction();

        try {
            (new AccountService())->unachive($account);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::info($e->getMessage());
            throw $e;
        }
    }
}
