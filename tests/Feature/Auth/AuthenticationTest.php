<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertNoRedirect();

    $this->assertGuest();
});

test('unverified user login redirects to verification notice', function () {
    $user = User::factory()->unverified()->create([
        'password' => \Illuminate\Support\Facades\Hash::make('Password123'),
    ]);

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'Password123');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('verification.notice', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('login is rate limited after 5 failed attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password')
            ->call('login');
    }

    Volt::test('pages.auth.login')
        ->set('form.email', $user->email)
        ->set('form.password', 'wrong-password')
        ->call('login')
        ->assertHasErrors(['form.email']);
});

test('admin user login redirects to admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'password' => \Illuminate\Support\Facades\Hash::make('AdminPassword123'),
        'email_verified_at' => now(),
    ]);

    $component = Volt::test('pages.auth.login')
        ->set('form.email', $admin->email)
        ->set('form.password', 'AdminPassword123');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin);
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
