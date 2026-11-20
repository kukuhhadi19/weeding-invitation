<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

test('password can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('profile.update-password-form')
        ->set('current_password', 'password')
        ->set('password', 'NewPassword123')
        ->set('password_confirmation', 'NewPassword123')
        ->call('updatePassword');

    $component
        ->assertHasNoErrors()
        ->assertNoRedirect();

    $this->assertTrue(Hash::check('NewPassword123', $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('profile.update-password-form')
        ->set('current_password', 'wrong-password')
        ->set('password', 'NewPassword123')
        ->set('password_confirmation', 'NewPassword123')
        ->call('updatePassword');

    $component
        ->assertHasErrors(['current_password'])
        ->assertNoRedirect();
});
