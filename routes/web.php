<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('login/magic/{user}', \App\Http\Controllers\Auth\MagicLoginController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('login.magic');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('dashboard', 'admin.dashboard')->name('dashboard');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
