<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Middleware\API\v1\VerifyStore;

//User
Route::middleware('web')->group(function () {
    Route::middleware(VerifyStore::class)->group(function () {

        // Owner Dashboard Route
        Route::get('dashboard/order/day', [OwnerDashboardController::class, 'order_day']);
        Route::get('dashboard/order/week', [OwnerDashboardController::class, 'order_week']);
        Route::get('dashboard/order/month', [OwnerDashboardController::class, 'order_month']);
        Route::get('dashboard/order/year', [OwnerDashboardController::class, 'order_year']);
        // Admin Dashboard Route
        Route::get('admin/dashboard/order/day', [AdminDashboardController::class, 'order_day']);
        Route::get('admin/dashboard/order/week', [AdminDashboardController::class, 'order_week']);
        Route::get('admin/dashboard/order/month', [AdminDashboardController::class, 'order_month']);
        Route::get('admin/dashboard/order/year', [AdminDashboardController::class, 'order_year']);

        Route::get('admin/dashboard/inventory/delta', [AdminDashboardController::class, 'inventory_delta']);
        Route::get('export/inventory', [InventoryExportController::class, 'export']);
    });
});
