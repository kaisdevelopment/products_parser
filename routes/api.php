<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'apiDetails']);

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{code}', [ProductController::class, 'show']);
    Route::put('/{code}', [ProductController::class, 'update']);
    Route::delete('/{code}', [ProductController::class, 'destroy']);
});