<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;

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

Route::group(['middleware'=> ['auth:sanctum']], function(){
    Route::post('/logout', [APIController::class, 'logout']);
    Route::post('/profile', [APIController::class, 'userProfile']);
    Route::post('/change-password', [APIController::class, 'changePassword']);
    Route::post('/purchase-order', [APIController::class, 'purchaseOrder']);
    Route::post('/sales-order', [APIController::class, 'salesOrder']);
    Route::post('/cash-bank', [APIController::class, 'cashBank']);
});

Route::post('/login', [APIController::class, 'login']); 