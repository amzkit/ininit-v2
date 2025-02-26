<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Dashboard');
    })->middleware(['auth'])->name('dashboard');
    Route::get('/dashboard/order', function () {    return Inertia::render('Dashboard/Order');  })->name('dashboard.order');
    Route::get('/dashboard/inventory', function () {    return Inertia::render('Dashboard/Inventory');  })->name('dashboard.inventory');

    Route::get('/product', function () {    return Inertia::render('Product/Index');  })->name('product.index');
    
    Route::get('/inventory', function () {    return Inertia::render('Inventory/Index');  })->name('inventory.index');





});

require __DIR__.'/auth.php';
