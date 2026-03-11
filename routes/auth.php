<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // OAuth Routes
    Route::get('login/microsoft', [AuthController::class, 'signin'])
        ->name('login.microsoft');

    Route::get('/callback', [AuthController::class, 'callback']);
    
    // Login route
    Route::get('login', function () {
        if (config('app.azure_auth_enabled')) {
            return redirect()->route('login.microsoft');
        }
        return inertia('Auth/Login');
    })->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('password-change', [ChangePasswordController::class, 'create'])
        ->name('password.change');
    
    Route::post('password-change', [ChangePasswordController::class, 'store']);

    Route::post('logout', [AuthController::class, 'signout'])
        ->name('logout');
});
