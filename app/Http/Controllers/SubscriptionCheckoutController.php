<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionTier;
use App\Services\SubscriptionCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class SubscriptionCheckoutController extends Controller
{
    /**
     * Generate a Midtrans Snap token for upgrading to a paid subscription tier.
     */
    public function __invoke(Request $request, SubscriptionCheckoutService $checkoutService): JsonResponse
    {
        $validated = $request->validate([
            'subscription_tier_id' => ['required', 'integer', 'exists:subscription_tiers,id'],
        ]);

        $tier = SubscriptionTier::findOrFail($validated['subscription_tier_id']);

        try {
            $transaction = $checkoutService->initiateCheckout($request->user(), $tier);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }

        return response()->json([
            'transaction_id' => $transaction->id,
            'order_id' => $transaction->midtrans_order_id,
            'snap_token' => $transaction->snap_token,
            'amount' => $transaction->amount,
        ], 201);
    }
}
