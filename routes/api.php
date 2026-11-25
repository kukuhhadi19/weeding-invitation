<?php

use App\Http\Controllers\Api\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', MidtransWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('api.midtrans.notification');
