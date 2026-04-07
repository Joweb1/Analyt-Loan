<?php

use App\Http\Controllers\Api\BorrowerController;
use App\Http\Controllers\Api\CollateralController;
use App\Http\Controllers\Api\LoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api'])->group(function () {
    Route::apiResource('collaterals', CollateralController::class);
    Route::apiResource('borrowers', BorrowerController::class);
    Route::apiResource('loans', LoanController::class);

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});
