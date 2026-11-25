<?php

use App\Models\SubscriptionTier;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\SubscriptionTierSeeder;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Volt;

test('checkout endpoint requires authenticated verified user', function () {
    $this->seed(SubscriptionTierSeeder::class);
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();

    $this->postJson(route('subscription.checkout'), [
        'subscription_tier_id' => $premiumTier->id,
    ])->assertUnauthorized();

    $unverified = User::factory()->unverified()->create();

    $this->actingAs($unverified)
        ->post(route('subscription.checkout'), [
            'subscription_tier_id' => $premiumTier->id,
        ])->assertRedirect(route('verification.notice'));
});

test('checkout endpoint rejects free tier and generates snap token for paid tier', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    Http::fake([
        '*midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'snap-token-12345',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-12345',
        ], 201),
    ]);

    $this->actingAs($user)
        ->postJson(route('subscription.checkout'), [
            'subscription_tier_id' => $freeTier->id,
        ])->assertUnprocessable();

    $response = $this->actingAs($user)
        ->postJson(route('subscription.checkout'), [
            'subscription_tier_id' => $premiumTier->id,
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['transaction_id', 'order_id', 'snap_token', 'amount'])
        ->assertJson([
            'snap_token' => 'snap-token-12345',
            'amount' => 99000,
        ]);

    $transaction = Transaction::where('user_id', $user->id)->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe(Transaction::STATUS_PENDING)
        ->and($transaction->snap_token)->toBe('snap-token-12345')
        ->and($transaction->subscription_tier_id)->toBe($premiumTier->id);
});

test('checkout marks transaction failed when midtrans api rejects request', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $basicTier = SubscriptionTier::where('slug', 'basic')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    Http::fake([
        '*midtrans.com/snap/v1/transactions' => Http::response([
            'error_messages' => ['Access denied due to unauthorized transaction'],
        ], 401),
    ]);

    $this->actingAs($user)
        ->postJson(route('subscription.checkout'), [
            'subscription_tier_id' => $basicTier->id,
        ])->assertStatus(503);

    $transaction = Transaction::where('user_id', $user->id)->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe(Transaction::STATUS_FAILED);
});

test('subscription volt component dispatches open-midtrans-snap event on checkout', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    Http::fake([
        '*midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'volt-snap-token-999',
        ], 201),
    ]);

    Volt::actingAs($user)
        ->test('pages.subscription')
        ->call('selectPlan', $premiumTier->id)
        ->assertSet('showCheckoutModal', true)
        ->call('proceedCheckout')
        ->assertSet('showCheckoutModal', false)
        ->assertDispatched('open-midtrans-snap');

    $transaction = Transaction::where('user_id', $user->id)->latest()->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->snap_token)->toBe('volt-snap-token-999');
});

test('transactions page shows only owner transactions and filters by status', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $basicTier = SubscriptionTier::where('slug', 'basic')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();

    $owner = User::factory()->create(['email_verified_at' => now()]);
    $otherUser = User::factory()->create(['email_verified_at' => now()]);

    $successTrx = Transaction::create([
        'user_id' => $owner->id,
        'subscription_tier_id' => $basicTier->id,
        'midtrans_order_id' => 'SUB-OWNER-SUCCESS-01',
        'amount' => $basicTier->price_monthly,
        'status' => Transaction::STATUS_SUCCESS,
        'payment_type' => 'qris',
        'paid_at' => now(),
    ]);

    $pendingTrx = Transaction::create([
        'user_id' => $owner->id,
        'subscription_tier_id' => $premiumTier->id,
        'midtrans_order_id' => 'SUB-OWNER-PENDING-02',
        'amount' => $premiumTier->price_monthly,
        'status' => Transaction::STATUS_PENDING,
        'snap_token' => 'resume-snap-token',
    ]);

    Transaction::create([
        'user_id' => $otherUser->id,
        'subscription_tier_id' => $premiumTier->id,
        'midtrans_order_id' => 'SUB-OTHER-PRIVATE-99',
        'amount' => $premiumTier->price_monthly,
        'status' => Transaction::STATUS_SUCCESS,
    ]);

    $this->get('/transactions')->assertRedirect('/login');

    $this->actingAs($owner)
        ->get('/transactions')
        ->assertOk()
        ->assertSee('SUB-OWNER-SUCCESS-01')
        ->assertSee('SUB-OWNER-PENDING-02')
        ->assertSee('Lanjutkan Bayar')
        ->assertDontSee('SUB-OTHER-PRIVATE-99');

    Volt::actingAs($owner)
        ->test('pages.transactions')
        ->call('setStatus', Transaction::STATUS_PENDING)
        ->assertSee('SUB-OWNER-PENDING-02')
        ->assertDontSee('SUB-OWNER-SUCCESS-01')
        ->call('continuePayment', $pendingTrx->id)
        ->assertDispatched('open-midtrans-snap');
});

test('admin dashboard renders transaction monitoring metrics matching template baseline', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    $customer = User::factory()->create([
        'role' => 'user',
        'subscription_tier_id' => $premiumTier->id,
        'email_verified_at' => now(),
    ]);

    Transaction::create([
        'user_id' => $customer->id,
        'subscription_tier_id' => $premiumTier->id,
        'midtrans_order_id' => 'SUB-ADMIN-MONITOR-01',
        'amount' => 99000,
        'status' => Transaction::STATUS_SUCCESS,
        'payment_type' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('Kelola Platform Undangan Digital Secara Terpusat')
        ->assertSee('Monitoring Transaksi Platform')
        ->assertSee('SUB-ADMIN-MONITOR-01')
        ->assertSee('Rp 99.000');

    $this->actingAs($customer)
        ->get('/admin/dashboard')
        ->assertForbidden();
});
