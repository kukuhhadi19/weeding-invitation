<?php

namespace Tests\Feature\Auth;

use Livewire\Volt\Volt;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.register');
});

test('new users can register and are verified immediately', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user->hasVerifiedEmail())->toBeTrue();
});

test('registration requires strong password', function () {
    $component = Volt::test('pages.auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'simple')
        ->set('password_confirmation', 'simple');

    $component->call('register');

    $component->assertHasErrors(['password']);
});

test('new user is assigned free tier by default', function () {
    \Database\Seeders\SubscriptionTierSeeder::class;
    $freeTier = \App\Models\SubscriptionTier::create([
        'name' => 'Free',
        'slug' => 'free',
        'price_monthly' => 0,
        'max_active_invitations' => 1,
        'max_gallery_photos' => 5,
    ]);

    $component = Volt::test('pages.auth.register')
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('password', 'Password123')
        ->set('password_confirmation', 'Password123');

    $component->call('register');

    $user = \App\Models\User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe('user');
    expect($user->subscription_tier_id)->toBe($freeTier->id);
});
