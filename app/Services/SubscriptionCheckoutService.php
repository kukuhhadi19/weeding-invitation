<?php

namespace App\Services;

use App\Models\SubscriptionTier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class SubscriptionCheckoutService
{
    public function __construct(
        private readonly MidtransService $midtransService,
    ) {}

    /**
     * Create a pending transaction and generate a Midtrans Snap token.
     */
    public function initiateCheckout(User $user, SubscriptionTier $tier): Transaction
    {
        if ((int) $tier->price_monthly <= 0) {
            throw new InvalidArgumentException('Paket gratis tidak memerlukan transaksi pembayaran.');
        }

        if (! $this->midtransService->isConfigured()) {
            throw new \RuntimeException('Kredensial Midtrans Sandbox belum diatur di file .env.');
        }

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'subscription_tier_id' => $tier->id,
            'midtrans_order_id' => sprintf('SUB-%d-%s-%s', $user->id, now()->format('YmdHis'), Str::upper(Str::random(6))),
            'amount' => (int) $tier->price_monthly,
            'status' => Transaction::STATUS_PENDING,
        ]);

        try {
            $snapToken = $this->midtransService->createSnapToken($transaction, $user, $tier);
            $transaction->update(['snap_token' => $snapToken]);
        } catch (Throwable $e) {
            $transaction->update(['status' => Transaction::STATUS_FAILED]);

            throw $e;
        }

        return $transaction->fresh(['subscriptionTier']);
    }

    /**
     * Verify and process an incoming Midtrans payment notification webhook idempotently.
     */
    public function handleNotification(array $payload): Transaction
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = isset($payload['fraud_status']) ? (string) $payload['fraud_status'] : null;

        if (! $this->midtransService->isValidSignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            abort(403, 'Signature Midtrans tidak valid.');
        }

        return DB::transaction(function () use ($payload, $orderId, $grossAmount, $transactionStatus, $fraudStatus): Transaction {
            $transaction = Transaction::where('midtrans_order_id', $orderId)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                abort(404, 'Transaksi tidak ditemukan.');
            }

            if ((int) round((float) $grossAmount) !== (int) $transaction->amount) {
                abort(422, 'Nominal transaksi tidak sesuai.');
            }

            $mappedStatus = $this->midtransService->mapTransactionStatus($transactionStatus, $fraudStatus);
            $wasAlreadySuccess = $transaction->isSuccess();

            $updateData = [
                'midtrans_transaction_id' => $payload['transaction_id'] ?? $transaction->midtrans_transaction_id,
                'payment_type' => $payload['payment_type'] ?? $transaction->payment_type,
                'midtrans_payload' => $payload,
            ];

            if (! $wasAlreadySuccess) {
                $updateData['status'] = $mappedStatus;

                if ($mappedStatus === Transaction::STATUS_SUCCESS) {
                    $updateData['paid_at'] = now();
                }
            }

            $transaction->update($updateData);

            if (! $wasAlreadySuccess && $mappedStatus === Transaction::STATUS_SUCCESS) {
                $this->activateUserSubscription($transaction);
            }

            return $transaction->fresh(['user', 'subscriptionTier']);
        });
    }

    /**
     * Upgrade or extend the owner's subscription tier upon verified payment settlement.
     */
    private function activateUserSubscription(Transaction $transaction): void
    {
        $user = User::whereKey($transaction->user_id)->lockForUpdate()->first();

        if (! $user) {
            return;
        }

        $isExtendingSameTier = (int) $user->subscription_tier_id === (int) $transaction->subscription_tier_id
            && $user->subscription_expires_at?->isFuture();

        $user->update([
            'subscription_tier_id' => $transaction->subscription_tier_id,
            'subscription_expires_at' => $isExtendingSameTier
                ? $user->subscription_expires_at->copy()->addMonth()
                : now()->addMonth(),
        ]);
    }
}
