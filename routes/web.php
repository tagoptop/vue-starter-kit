<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConstructionDashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [ConstructionDashboardController::class, 'index'])->name('dashboard');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

    Route::middleware('role:customer')->group(function () {
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('orders/cart/add', [OrderController::class, 'addToCart'])->name('orders.cart.add');
        Route::patch('orders/cart/{product}', [OrderController::class, 'updateCart'])->name('orders.cart.update');
        Route::delete('orders/cart/{product}', [OrderController::class, 'removeFromCart'])->name('orders.cart.remove');
        Route::post('orders/cart/clear', [OrderController::class, 'clearCart'])->name('orders.cart.clear');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
        Route::post('inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('orders.update-status');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserManagementController::class)->only(['index', 'edit', 'update']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
