<?php

use App\Http\Controllers\Auth\MagicLoginController;
use App\Http\Controllers\SubscriptionCheckoutController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::get('login/magic/{user}', MagicLoginController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('login.magic');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Volt::route('subscription', 'pages.subscription')
    ->middleware(['auth', 'verified'])
    ->name('subscription.index');

Route::post('subscription/checkout', SubscriptionCheckoutController::class)
    ->middleware(['auth', 'verified'])
    ->name('subscription.checkout');

Volt::route('transactions', 'pages.transactions')
    ->middleware(['auth', 'verified'])
    ->name('transactions.index');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('dashboard', 'admin.dashboard')->name('dashboard');
    Volt::route('categories', 'pages.admin.categories')->name('categories.index');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
