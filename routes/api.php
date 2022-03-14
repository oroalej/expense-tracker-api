<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

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

    Route::apiResources(
        [
            'category' => CategoryController::class,
            'tag' => TagController::class,
            'transaction' => TransactionController::class,
            'wallet' => WalletController::class,
        ],
        ['except' => ['index', 'show']]
    );
});
