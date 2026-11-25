<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming Midtrans payment notification webhook.
     */
    public function __invoke(Request $request, SubscriptionCheckoutService $checkoutService): JsonResponse
    {
        $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required'],
            'gross_amount' => ['required'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
        ]);

        $transaction = $checkoutService->handleNotification($request->all());

        return response()->json([
            'message' => 'Notifikasi pembayaran berhasil diproses.',
            'order_id' => $transaction->midtrans_order_id,
            'status' => $transaction->status,
        ]);
    }
}
