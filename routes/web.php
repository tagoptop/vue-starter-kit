<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ConstructionDashboardController;
use App\Http\Controllers\AssistantComposeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [ConstructionDashboardController::class, 'index'])
        ->middleware('role:admin,staff,driver,warehouseman,checker')
        ->name('dashboard');
    Route::get('assistant/compose', AssistantComposeController::class)->name('assistant.compose');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::resource('conversations', ConversationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

    Route::middleware('role:customer')->group(function () {
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::post('orders/cart/add', [OrderController::class, 'addToCart'])->name('orders.cart.add');
        Route::patch('orders/cart/{product}', [OrderController::class, 'updateCart'])->name('orders.cart.update');
        Route::delete('orders/cart/{product}', [OrderController::class, 'removeFromCart'])->name('orders.cart.remove');
        Route::post('orders/cart/clear', [OrderController::class, 'clearCart'])->name('orders.cart.clear');
    });

    Route::middleware('role:driver')->group(function () {
        Route::get('driver/deliveries', [OrderController::class, 'driverDeliveries'])->name('driver.deliveries.index');
    });

    Route::middleware('role:warehouseman')->group(function () {
        Route::get('warehouse/preparation', [OrderController::class, 'warehousePreparation'])->name('warehouse.preparation');
        Route::patch('warehouse/preparation/items/{orderItem}/mark-prepared', [OrderController::class, 'markWarehouseItemPrepared'])
            ->whereNumber('orderItem')
            ->name('warehouse.preparation.items.mark-prepared');
    });

    Route::middleware('role:checker')->group(function () {
        Route::get('checker/spot-checks', [OrderController::class, 'checkerSpotChecks'])->name('checker.spot-checks');
    });

    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('deliveries', [OrderController::class, 'deliveryMonitoring'])->name('deliveries.index');
        Route::get('deliveries/weekly', [OrderController::class, 'weeklySchedule'])->name('deliveries.weekly');
        Route::post('inventory/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stock-in');
        Route::post('inventory/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stock-out');
        Route::patch('deliveries/reorder', [OrderController::class, 'reorderDeliveries'])->name('deliveries.reorder');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('orders.update-status');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    });

    Route::get('orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::get('orders/{order}/receipt', [OrderController::class, 'deliveryReceipt'])->whereNumber('order')->name('orders.receipt');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserManagementController::class)->only(['index', 'edit', 'update']);
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
