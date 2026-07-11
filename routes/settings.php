<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\CompanyBrandingController;
use App\Http\Controllers\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('settings.profile');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('settings.password');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', [SettingsController::class, 'appearance'])->name('settings.appearance');

    // Company branding settings (admin only)
    Route::get('settings/branding', [CompanyBrandingController::class, 'edit'])->name('settings.branding');
    Route::post('settings/branding', [CompanyBrandingController::class, 'update'])->name('branding.update');
    Route::delete('settings/branding', [CompanyBrandingController::class, 'reset'])->name('branding.reset');
});
