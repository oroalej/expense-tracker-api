<?php

use App\Http\Controllers;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ArchiveAccountController;
use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\Budget\AutoAssignController;
use App\Http\Controllers\Budget\BudgetController;
use App\Http\Controllers\BudgetCategory\BudgetCategoryController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\Transaction\ApproveTransactionController;
use App\Http\Controllers\Transaction\ClearTransactionController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Middleware\VerifyLedgerId;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(static function () {
    Route::post('sanctum/token', [Controllers\Auth\AuthController::class, 'token']);
    Route::post('/register', Controllers\Auth\RegisterController::class);
    Route::post('/reset-password', Controllers\Auth\NewPasswordController::class);
    Route::get('/verify-email/{id}/{hash}', Controllers\Auth\VerifyEmailController::class)
        ->middleware(['auth', 'signed', 'throttle:6,1']);
});

Route::middleware('auth:sanctum')->group(static function () {
    Route::get('ledgers', [LedgerController::class, 'index']);
    Route::post('logout', [Controllers\Auth\AuthController::class, 'logout']);

    Route::middleware(VerifyLedgerId::class)
        ->group(static function () {
            Route::get('account-types', AccountTypeController::class);

            Route::apiResource('ledgers', LedgerController::class)->except('index');
            Route::apiResource('budgets', BudgetController::class)->only('index', 'show');
            Route::get('budgets/{budget}/budget-categories', [BudgetCategoryController::class, 'index']);

            Route::prefix('budget-categories')->group(static function () {
                Route::get('{budgetCategory}', [BudgetCategoryController::class, 'show']);
                Route::put('{budgetCategory}', [BudgetCategoryController::class, 'update']);
                Route::get('auto-assign', [AutoAssignController::class, 'index']);
                Route::get('auto-assign/{category}', [AutoAssignController::class, 'show']);
            });

            Route::apiResource('accounts', AccountController::class);
            Route::prefix('accounts/{account}')->group(static function () {
                Route::post('archived', [ArchiveAccountController::class, 'store']);
                Route::post('unarchive', [ArchiveAccountController::class, 'destroy']);
            });

            Route::apiResource('categories', CategoryController::class)->except('edit');

            Route::apiResource('categories', CategoryController::class)->except('store', 'edit');

            Route::prefix('categories/{category}')->group(static function () {
                Route::post('actions', Controllers\Category\CategoryActionController::class);
                Route::post('change-parent-category', Controllers\Category\ChangeParentCategoryController::class);
            });

            Route::apiResource('transactions', TransactionController::class);
            Route::prefix('transactions/{transaction}')->group(static function () {
                Route::post('approved', [ApproveTransactionController::class, 'store']);
                Route::post('rejected', [ApproveTransactionController::class, 'destroy']);
                Route::post('cleared', [ClearTransactionController::class, 'store']);
                Route::post('uncleared', [ClearTransactionController::class, 'destroy']);
            });
        });
});
