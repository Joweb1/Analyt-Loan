<?php

use App\Http\Controllers\Api\CollateralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('collaterals', CollateralController::class);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
