<?php

use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\OutgoingProductController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('outgoing-products', [OutgoingProductController::class, 'index'])->name('outgoing-products.index');
    Route::post('outgoing-products', [OutgoingProductController::class, 'store'])->name('outgoing-products.store');
    Route::patch('outgoing-products/{outgoingProduct}/release', [OutgoingProductController::class, 'release'])
        ->name('outgoing-products.release');
    Route::patch('outgoing-products/{outgoingProduct}/deliver', [OutgoingProductController::class, 'deliver'])
        ->name('outgoing-products.deliver');

    Route::get('admin/roles', [RoleManagementController::class, 'index'])
        ->middleware('admin')
        ->name('admin.roles.index');
    Route::patch('admin/roles/{user}', [RoleManagementController::class, 'update'])
        ->middleware('admin')
        ->name('admin.roles.update');
});

require __DIR__.'/settings.php';
