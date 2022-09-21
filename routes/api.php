<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ArchiveAccountController;
use App\Http\Controllers\Account\UnarchiveAccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Category\ChangeCategoryGroupController;
use App\Http\Controllers\Category\HideCategoryController;
use App\Http\Controllers\Category\UnhideCategoryController;
use App\Http\Controllers\CategoryGroup\CategoryGroupController;
use App\Http\Controllers\CategoryGroup\HideCategoryGroupController;
use App\Http\Controllers\CategoryGroup\UnhideCategoryGroupController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\Transaction\TransactionActionsController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Middleware\VerifyLedgerUuid;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(static function () {
    Route::post('sanctum/token', [AuthController::class, 'token'])->name(
        'sanctum.token'
    );

    Route::post('/register', RegisterController::class)->name('register');

    Route::post('/reset-password', NewPasswordController::class)->name(
        'password.update'
    );

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['auth', 'signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::middleware('auth:sanctum')->group(static function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::apiResource('ledgers', LedgerController::class)->except([
        'index',
        'show',
    ]);

    Route::middleware([VerifyLedgerUuid::class])->group(static function () {
        Route::apiResources(
            [
                'category-groups'            => CategoryGroupController::class,
                'category-groups.categories' => CategoryController::class,
                'accounts'                   => AccountController::class,
                'accounts.transactions'      => TransactionController::class,
            ],
            [
                'except'     => ['show'],
                'parameters' => ['category_groups' => 'categoryGroup'],
            ]
        );

        Route::prefix('accounts/{account}')
            ->name('accounts.')
            ->group(static function () {
                Route::post('archived', ArchiveAccountController::class)->name('archive');
                Route::post('unarchive', UnarchiveAccountController::class)->name('unarchive');
            });

        Route::prefix('category-groups/{category_group}')
            ->name('category-groups.')
            ->group(static function () {
                Route::post('hide', HideCategoryGroupController::class)->name('hide');
                Route::post('unhide', UnhideCategoryGroupController::class)->name('unhide');
            });

        Route::prefix('categories/{category}')
            ->name('categories.')
            ->group(static function () {
                Route::post('hide', HideCategoryController::class)->name('hide');
                Route::post('unhide', UnhideCategoryController::class)->name('unhide');
                Route::post('change-category-group', ChangeCategoryGroupController::class)
                    ->name('change-category-group');
            });

        Route::prefix('transactions/{transaction}')
            ->name('transactions.actions.')
            ->controller(TransactionActionsController::class)
            ->group(static function () {
                Route::post('approved', 'approved')->name('approved');
                Route::post('rejected', 'rejected')->name('rejected');
                Route::post('cleared', 'cleared')->name('cleared');
                Route::post('uncleared', 'uncleared')->name('uncleared');
            });
    });
});
