<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\MagicLoginNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Volt;

test('magic link can be requested for registered user', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'magic@wedding.test',
    ]);

    Volt::test('pages.auth.login')
        ->set('form.email', 'magic@wedding.test')
        ->call('sendMagicLink')
        ->assertHasNoErrors()
        ->assertSee('Tautan masuk berhasil dikirim');

    Notification::assertSentTo($user, MagicLoginNotification::class);
});

test('magic link cannot be requested for unregistered email', function () {
    Notification::fake();

    Volt::test('pages.auth.login')
        ->set('form.email', 'unknown@wedding.test')
        ->call('sendMagicLink')
        ->assertHasErrors(['form.email']);

    Notification::assertNothingSent();
});

test('user can authenticate using valid magic link', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'unverified@wedding.test',
    ]);

    $url = URL::temporarySignedRoute(
        'login.magic',
        now()->addMinutes(15),
        ['user' => $user->id]
    );

    $response = $this->get($url);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('invalid magic link signature is rejected', function () {
    $user = User::factory()->create();

    $response = $this->get(route('login.magic', ['user' => $user->id]).'?signature=invalid');

    $response->assertForbidden();
    $this->assertGuest();
});
