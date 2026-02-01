<?php

use App\Http\Controllers\TransactionController;
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
| TASK: Implement the following API endpoints as part of the assessment:
| - POST   /api/v1/transactions          - Process a new transaction
| - GET    /api/v1/transactions/{id}     - Get transaction details
| - GET    /api/v1/sellers/{id}/commission-summary - Get seller commission summary
|
*/

Route::prefix('v1')->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});
