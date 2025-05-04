<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\PropertyController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('property', PropertyController::class);
        Route::post('property/bulk-upload', [PropertyController::class, 'bulkUpload']);
    });
    Route::get('/batch-info/{id}', [PropertyController::class, 'batchInfo']);
});
