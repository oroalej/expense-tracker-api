<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
	return $request->user();
});

Route::middleware('auth:sanctum')->group(static function () {
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
