<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\StockMovementController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UnitOfMeasureController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('auth/signup', [AuthController::class, 'signup'])->name('auth.signup');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('auth/profile', [AuthController::class, 'updateProfile'])->name('auth.profile.update');

        Route::get('products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');

        Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
        Route::apiResource('units-of-measure', UnitOfMeasureController::class)->only(['index', 'show'])->parameters(['units-of-measure' => 'unit_of_measure']);
        Route::apiResource('suppliers', SupplierController::class)->only(['index', 'show']);
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'show', 'store']);

        Route::middleware('role:admin')->group(function () {
            Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
            Route::apiResource('units-of-measure', UnitOfMeasureController::class)->only(['store', 'update', 'destroy'])->parameters(['units-of-measure' => 'unit_of_measure']);
            Route::apiResource('suppliers', SupplierController::class)->only(['store', 'update', 'destroy']);
            Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('inventory-summary', [ReportController::class, 'summary'])->name('summary');
                Route::get('stock-value', [ReportController::class, 'stockValue'])->name('stock-value');
            });
        });
    });
});
