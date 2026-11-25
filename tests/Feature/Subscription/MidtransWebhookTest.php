<?php

use App\Models\SubscriptionTier;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
use Database\Seeders\SubscriptionTierSeeder;

function makeMidtransSignature(string $orderId, string $statusCode, string $grossAmount): string
{
    $serverKey = (string) config('midtrans.server_key');

    return hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
}

test('midtrans service validates sha512 signatures and maps statuses accurately', function () {
    $service = app(MidtransService::class);
    $validSig = makeMidtransSignature('ORD-1', '200', '99000.00');

    expect($service->isValidSignature('ORD-1', '200', '99000.00', $validSig))->toBeTrue()
        ->and($service->isValidSignature('ORD-1', '200', '99001.00', $validSig))->toBeFalse()
        ->and($service->isValidSignature('ORD-1', '200', '99000.00', 'tampered'))->toBeFalse();

    expect($service->mapTransactionStatus('settlement'))->toBe(Transaction::STATUS_SUCCESS)
        ->and($service->mapTransactionStatus('capture', 'accept'))->toBe(Transaction::STATUS_SUCCESS)
        ->and($service->mapTransactionStatus('capture', 'challenge'))->toBe(Transaction::STATUS_PENDING)
        ->and($service->mapTransactionStatus('capture', 'deny'))->toBe(Transaction::STATUS_FAILED)
        ->and($service->mapTransactionStatus('pending'))->toBe(Transaction::STATUS_PENDING)
        ->and($service->mapTransactionStatus('expire'))->toBe(Transaction::STATUS_EXPIRED)
        ->and($service->mapTransactionStatus('deny'))->toBe(Transaction::STATUS_FAILED)
        ->and($service->mapTransactionStatus('cancel'))->toBe(Transaction::STATUS_FAILED);
});

test('midtrans webhook rejects invalid signature and amount mismatch', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'subscription_tier_id' => $premiumTier->id,
        'midtrans_order_id' => 'SUB-INVALID-SIG-01',
        'amount' => 99000,
        'status' => Transaction::STATUS_PENDING,
    ]);

    $this->postJson('/api/midtrans/notification', [
        'order_id' => $transaction->midtrans_order_id,
        'status_code' => '200',
        'gross_amount' => '99000.00',
        'signature_key' => 'invalid-signature',
        'transaction_status' => 'settlement',
    ])->assertForbidden();

    expect($transaction->fresh()->status)->toBe(Transaction::STATUS_PENDING)
        ->and($user->fresh()->subscription_tier_id)->toBe($freeTier->id);

    $tamperedAmount = '1000.00';
    $this->postJson('/api/midtrans/notification', [
        'order_id' => $transaction->midtrans_order_id,
        'status_code' => '200',
        'gross_amount' => $tamperedAmount,
        'signature_key' => makeMidtransSignature($transaction->midtrans_order_id, '200', $tamperedAmount),
        'transaction_status' => 'settlement',
    ])->assertUnprocessable();

    expect($transaction->fresh()->status)->toBe(Transaction::STATUS_PENDING);
});

test('midtrans webhook upgrades user tier idempotently on settlement', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'subscription_tier_id' => $premiumTier->id,
        'midtrans_order_id' => 'SUB-SETTLE-01',
        'amount' => 99000,
        'status' => Transaction::STATUS_PENDING,
    ]);

    $payload = [
        'order_id' => $transaction->midtrans_order_id,
        'transaction_id' => 'mt-trx-001',
        'status_code' => '200',
        'gross_amount' => '99000.00',
        'payment_type' => 'qris',
        'transaction_status' => 'settlement',
        'signature_key' => makeMidtransSignature($transaction->midtrans_order_id, '200', '99000.00'),
    ];

    $this->postJson('/api/midtrans/notification', $payload)
        ->assertOk()
        ->assertJson([
            'order_id' => 'SUB-SETTLE-01',
            'status' => Transaction::STATUS_SUCCESS,
        ]);

    $user->refresh();
    $transaction->refresh();

    expect($transaction->status)->toBe(Transaction::STATUS_SUCCESS)
        ->and($transaction->payment_type)->toBe('qris')
        ->and($transaction->midtrans_transaction_id)->toBe('mt-trx-001')
        ->and($transaction->paid_at)->not->toBeNull()
        ->and($user->subscription_tier_id)->toBe($premiumTier->id)
        ->and($user->subscription_expires_at)->not->toBeNull();

    $firstExpiry = $user->subscription_expires_at->toIso8601String();

    // Replay exact same webhook notification (idempotency check)
    $this->travel(5)->minutes();
    $this->postJson('/api/midtrans/notification', $payload)->assertOk();

    expect($user->fresh()->subscription_expires_at->toIso8601String())->toBe($firstExpiry);
});

test('midtrans webhook handles pending, expire, deny, and cancel without upgrading user tier', function (string $midtransStatus, string $expectedStatus) {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $basicTier = SubscriptionTier::where('slug', 'basic')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'subscription_tier_id' => $basicTier->id,
        'midtrans_order_id' => 'SUB-STATUS-'.strtoupper($midtransStatus),
        'amount' => 49000,
        'status' => Transaction::STATUS_PENDING,
    ]);

    $statusCode = $midtransStatus === 'pending' ? '201' : '202';
    $grossAmount = '49000.00';

    $this->postJson('/api/midtrans/notification', [
        'order_id' => $transaction->midtrans_order_id,
        'transaction_id' => 'mt-status-'.$midtransStatus,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'payment_type' => 'bank_transfer',
        'transaction_status' => $midtransStatus,
        'signature_key' => makeMidtransSignature($transaction->midtrans_order_id, $statusCode, $grossAmount),
    ])->assertOk()
        ->assertJson([
            'status' => $expectedStatus,
        ]);

    expect($transaction->fresh()->status)->toBe($expectedStatus)
        ->and($transaction->fresh()->paid_at)->toBeNull()
        ->and($user->fresh()->subscription_tier_id)->toBe($freeTier->id);
})->with([
    'pending' => ['pending', Transaction::STATUS_PENDING],
    'expire' => ['expire', Transaction::STATUS_EXPIRED],
    'deny' => ['deny', Transaction::STATUS_FAILED],
    'cancel' => ['cancel', Transaction::STATUS_FAILED],
]);
