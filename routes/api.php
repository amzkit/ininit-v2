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

        Route::get('product', [ProductController::class, 'index']);
        Route::post('product', [ProductController::class, 'create']);
        Route::put('product/{id}', [ProductController::class, 'update']);
        Route::delete('product/{id}', [ProductController::class,'destroy']);

        Route::get('inventory', [InventoryController::class, 'index']);
        Route::post('inventory/update', [InventoryController::class, 'update']);
        Route::post('inventory/order', [InventoryController::class, 'saveOrder']);
        Route::post('inventory/in_out', [InventoryController::class, 'in_out']);

        //Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);
        //Route::get('/telegram/send', [TelegramBotController::class, 'sendMessage']);

        Route::prefix('partner')->group(function () {
            Route::get('/search', [PartnerController::class, 'search']); // Search partners
            Route::post('/create', [PartnerController::class, 'store']); // Create a new partner
            Route::put('/update/{id}', [PartnerController::class, 'update']); // Update a partner
        });

        Route::get('/notification/inventory', [NotificationController::class, 'inventory']);
    });
});
