<?php

use App\Http\Controllers\Api\ApiDeliveryController;
use App\Http\Controllers\Api\ApiProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('products', [ApiProductController::class, 'index']);
    Route::post('deliveries/{id}/confirm', [ApiDeliveryController::class, 'confirm']);
});
