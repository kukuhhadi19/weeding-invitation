<?php

namespace App\Services;

use App\Models\SubscriptionTier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    /**
     * Check whether Midtrans Sandbox/Production keys are configured.
     */
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'))
            && filled(config('midtrans.client_key'));
    }

    /**
     * Request a Midtrans Snap token for the given pending transaction.
     */
    public function createSnapToken(Transaction $transaction, User $user, SubscriptionTier $tier): string
    {
        $serverKey = (string) config('midtrans.server_key');
        $snapApiUrl = (string) config('midtrans.snap_api_url');

        if ($serverKey === '' || $snapApiUrl === '') {
            throw new RuntimeException('Kredensial Midtrans belum dikonfigurasi pada server.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $transaction->midtrans_order_id,
                'gross_amount' => (int) $transaction->amount,
            ],
            'item_details' => [
                [
                    'id' => $tier->slug,
                    'price' => (int) $transaction->amount,
                    'quantity' => 1,
                    'name' => Str::limit('Paket '.$tier->name.' (1 Bulan)', 50, ''),
                ],
            ],
            'customer_details' => [
                'first_name' => Str::limit(trim($user->name), 50, ''),
                'email' => $user->email,
            ],
            'credit_card' => [
                'secure' => (bool) config('midtrans.is_3ds', true),
            ],
            'callbacks' => [
                'finish' => route('transactions.index', ['order' => $transaction->midtrans_order_id]),
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post($snapApiUrl, $payload);

        $token = $response->json('token');

        if ($response->failed() || ! is_string($token) || $token === '') {
            Log::error('Midtrans Snap token generation failed', [
                'order_id' => $transaction->midtrans_order_id,
                'http_status' => $response->status(),
                'error_messages' => $response->json('error_messages'),
            ]);

            throw new RuntimeException('Gagal membuat sesi pembayaran Midtrans. Silakan coba beberapa saat lagi.');
        }

        return $token;
    }

    /**
     * Verify Midtrans SHA-512 webhook signature against configured server key.
     */
    public function isValidSignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '' || $orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Map Midtrans notification status and fraud status to internal transaction status.
     */
    public function mapTransactionStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        $status = strtolower(trim($transactionStatus));
        $fraud = $fraudStatus !== null ? strtolower(trim($fraudStatus)) : null;

        return match ($status) {
            'settlement' => Transaction::STATUS_SUCCESS,
            'capture' => match ($fraud) {
                'challenge' => Transaction::STATUS_PENDING,
                'deny' => Transaction::STATUS_FAILED,
                default => Transaction::STATUS_SUCCESS,
            },
            'pending' => Transaction::STATUS_PENDING,
            'expire' => Transaction::STATUS_EXPIRED,
            'deny', 'cancel', 'failure' => Transaction::STATUS_FAILED,
            default => Transaction::STATUS_PENDING,
        };
    }
}
