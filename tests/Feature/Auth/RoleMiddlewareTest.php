<?php

namespace Tests\Feature\Auth;

use App\Models\User;

test('admin can access admin dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertOk();
    $response->assertSee('Admin Dashboard');
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertForbidden();
});

test('user can access dashboard without email verification barrier', function () {
    $user = User::factory()->unverified()->create([
        'role' => 'user',
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Selamat datang, '.$user->name);
});

test('verified user can access dashboard and sees tier info', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Selamat datang, '.$user->name);
});
