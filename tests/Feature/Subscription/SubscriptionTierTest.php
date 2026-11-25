<?php

use App\Models\SubscriptionTier;
use App\Models\User;
use App\Services\SubscriptionLimitService;
use Database\Seeders\SubscriptionTierSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\Volt\Volt;

function createTempInvitationsTable(): void
{
    if (! Schema::hasTable('invitations')) {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }
}

test('subscription tier seeder creates four tiers with accurate limits and array features', function () {
    $this->seed(SubscriptionTierSeeder::class);

    expect(SubscriptionTier::count())->toBe(4);

    $free = SubscriptionTier::where('slug', 'free')->first();
    $basic = SubscriptionTier::where('slug', 'basic')->first();
    $premium = SubscriptionTier::where('slug', 'premium')->first();
    $enterprise = SubscriptionTier::where('slug', 'enterprise')->first();

    expect($free->price_monthly)->toBe(0)
        ->and($free->max_active_invitations)->toBe(1)
        ->and($free->max_gallery_photos)->toBe(5)
        ->and($free->features_json)->toBeArray()
        ->and($free->features_json['watermark'])->toBeTrue();

    expect($basic->price_monthly)->toBe(49000)
        ->and($basic->max_active_invitations)->toBe(3)
        ->and($basic->max_gallery_photos)->toBe(15)
        ->and($basic->remove_watermark)->toBeTrue();

    expect($premium->price_monthly)->toBe(99000)
        ->and($premium->max_active_invitations)->toBe(10)
        ->and($premium->max_gallery_photos)->toBe(30)
        ->and($premium->custom_slug)->toBeTrue();

    expect($enterprise->price_monthly)->toBe(249000)
        ->and($enterprise->max_active_invitations)->toBe(-1)
        ->and($enterprise->max_gallery_photos)->toBe(-1)
        ->and($enterprise->priority_support)->toBeTrue();
});

test('subscription limit service enforces active invitation quotas per tier', function () {
    $this->seed(SubscriptionTierSeeder::class);
    $service = app(SubscriptionLimitService::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $basicTier = SubscriptionTier::where('slug', 'basic')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $enterpriseTier = SubscriptionTier::where('slug', 'enterprise')->first();

    $freeUser = User::factory()->create(['subscription_tier_id' => $freeTier->id]);
    expect($service->canCreateInvitation($freeUser, 0))->toBeTrue()
        ->and($service->canCreateInvitation($freeUser, 1))->toBeFalse()
        ->and($service->hasReachedInvitationLimit($freeUser, 1))->toBeTrue()
        ->and($service->remainingInvitations($freeUser, 1))->toBe(0);

    $basicUser = User::factory()->create(['subscription_tier_id' => $basicTier->id]);
    expect($service->canCreateInvitation($basicUser, 2))->toBeTrue()
        ->and($service->canCreateInvitation($basicUser, 3))->toBeFalse()
        ->and($service->remainingInvitations($basicUser, 2))->toBe(1);

    $premiumUser = User::factory()->create(['subscription_tier_id' => $premiumTier->id]);
    expect($service->canCreateInvitation($premiumUser, 9))->toBeTrue()
        ->and($service->canCreateInvitation($premiumUser, 10))->toBeFalse();

    $enterpriseUser = User::factory()->create(['subscription_tier_id' => $enterpriseTier->id]);
    $summary = $service->getQuotaSummary($enterpriseUser, 50);
    expect($service->canCreateInvitation($enterpriseUser, 50))->toBeTrue()
        ->and($service->remainingInvitations($enterpriseUser, 50))->toBe(-1)
        ->and($summary['is_unlimited'])->toBeTrue()
        ->and($summary['limit_reached'])->toBeFalse();
});

test('subscription limit service excludes archived invitations and falls back when paid plan expired', function () {
    $this->seed(SubscriptionTierSeeder::class);
    createTempInvitationsTable();

    $service = app(SubscriptionLimitService::class);
    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();

    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
    ]);

    DB::table('invitations')->insert([
        ['user_id' => $user->id, 'status' => 'archived', 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect($service->countActiveInvitations($user))->toBe(0)
        ->and($service->canCreateInvitation($user))->toBeTrue();

    DB::table('invitations')->insert([
        ['user_id' => $user->id, 'status' => 'published', 'created_at' => now(), 'updated_at' => now()],
    ]);

    expect($service->countActiveInvitations($user))->toBe(1)
        ->and($service->hasReachedInvitationLimit($user))->toBeTrue();

    $expiredUser = User::factory()->create([
        'subscription_tier_id' => $premiumTier->id,
        'subscription_expires_at' => now()->subDay(),
    ]);

    expect($service->resolveTier($expiredUser)->slug)->toBe('free');
});

test('subscription limit service validates gallery photos, custom slug, and template access', function () {
    $this->seed(SubscriptionTierSeeder::class);
    $service = app(SubscriptionLimitService::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $basicTier = SubscriptionTier::where('slug', 'basic')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $enterpriseTier = SubscriptionTier::where('slug', 'enterprise')->first();

    $freeUser = User::factory()->create(['subscription_tier_id' => $freeTier->id]);
    $basicUser = User::factory()->create(['subscription_tier_id' => $basicTier->id]);
    $premiumUser = User::factory()->create(['subscription_tier_id' => $premiumTier->id]);
    $enterpriseUser = User::factory()->create(['subscription_tier_id' => $enterpriseTier->id]);

    expect($service->canUploadGalleryPhoto($freeUser, 4))->toBeTrue()
        ->and($service->canUploadGalleryPhoto($freeUser, 5))->toBeFalse()
        ->and($service->canUploadGalleryPhoto($enterpriseUser, 500))->toBeTrue();

    expect($service->canUseCustomSlug($freeUser))->toBeFalse()
        ->and($service->canUseCustomSlug($basicUser))->toBeFalse()
        ->and($service->canUseCustomSlug($premiumUser))->toBeTrue()
        ->and($service->canUseCustomSlug($enterpriseUser))->toBeTrue();

    expect($service->canUseTemplate($freeUser, false))->toBeTrue()
        ->and($service->canUseTemplate($freeUser, true))->toBeFalse()
        ->and($service->canUseTemplate($basicUser, true))->toBeTrue();
});

test('subscription page requires authenticated and verified user', function () {
    $this->get('/subscription')->assertRedirect('/login');

    $unverified = User::factory()->unverified()->create();
    $this->actingAs($unverified)
        ->get('/subscription')
        ->assertRedirect('/verify-email');
});

test('subscription page renders four database tiers and highlights current plan', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $basicTier = SubscriptionTier::where('slug', 'basic')->first();
    $premiumTier = SubscriptionTier::where('slug', 'premium')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $basicTier->id,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/subscription');

    $response->assertOk()
        ->assertSeeVolt('pages.subscription')
        ->assertSee('Free')
        ->assertSee('Basic')
        ->assertSee('Premium')
        ->assertSee('Enterprise')
        ->assertSee('Rp 0')
        ->assertSee('Rp 49.000')
        ->assertSee('Rp 99.000')
        ->assertSee('Rp 249.000')
        ->assertSee('Paket Anda Saat Ini');

    Volt::actingAs($user)
        ->test('pages.subscription')
        ->call('selectPlan', $premiumTier->id)
        ->assertSet('showCheckoutModal', true)
        ->assertSee('Konfirmasi Pilihan Paket')
        ->assertSee('Midtrans Snap');
});

test('dashboard disables create invitation button and shows upgrade cta when quota reached', function () {
    $this->seed(SubscriptionTierSeeder::class);
    createTempInvitationsTable();

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    // Under quota (0 / 1)
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Buat Undangan')
        ->assertDontSee('Batas Undangan Aktif Tercapai');

    // Reach quota (1 / 1)
    DB::table('invitations')->insert([
        ['user_id' => $user->id, 'status' => 'published', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertSee('Batas Undangan Aktif Tercapai')
        ->assertSee('Upgrade Paket')
        ->assertSeeHtml('disabled');
});

test('invitation quota middleware blocks access when active invitation limit is reached', function () {
    $this->seed(SubscriptionTierSeeder::class);
    createTempInvitationsTable();

    Route::middleware(['web', 'auth', 'invitation.quota'])
        ->get('/_test/invitations/create', fn () => response('allowed', 200));

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/_test/invitations/create')
        ->assertOk()
        ->assertSee('allowed');

    DB::table('invitations')->insert([
        ['user_id' => $user->id, 'status' => 'draft', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $this->actingAs($user)
        ->get('/_test/invitations/create')
        ->assertRedirect(route('subscription.index'));

    $this->actingAs($user)
        ->getJson('/_test/invitations/create')
        ->assertForbidden();
});

test('dashboard and subscription pages render unified sidebar and sticky header', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Menu Utama')
        ->assertSee('Langganan & Paket')
        ->assertSee('Pengaturan Akun')
        ->assertSee('Status Langganan dan Kuota')
        ->assertSee('Paket Free')
        ->assertSee('Email Terverifikasi')
        ->assertSee('Keluar');

    $this->actingAs($user)
        ->get('/subscription')
        ->assertOk()
        ->assertSee('aria-label="Kembali"', false)
        ->assertSee('Langganan & Paket');
});

test('profile account page renders dashboard-account sections and subscription quota', function () {
    $this->seed(SubscriptionTierSeeder::class);

    $freeTier = SubscriptionTier::where('slug', 'free')->first();
    $user = User::factory()->create([
        'subscription_tier_id' => $freeTier->id,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertSee('aria-label="Kembali"', false)
        ->assertSee('Pengaturan Akun')
        ->assertSee('Langganan & Kuota', false)
        ->assertSee('Paket Free')
        ->assertSee('Informasi Profil')
        ->assertSee('Ubah Kata Sandi')
        ->assertSee('Tindakan Akun')
        ->assertSee('Keluar')
        ->assertSee('Hapus Akun');
});
