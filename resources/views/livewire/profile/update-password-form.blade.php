<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="p-5 md:p-6 border-b border-border">
        <h3 class="font-bold text-lg text-foreground">Ubah Kata Sandi</h3>
        <p class="text-sm text-secondary mt-1">Pastikan akun Anda menggunakan kata sandi minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</p>
    </div>

    <form wire:submit="updatePassword" class="p-5 md:p-6 flex flex-col gap-5">
        <div class="flex flex-col gap-2">
            <label for="update_password_current_password" class="text-sm font-medium text-secondary">Kata Sandi Saat Ini</label>
            <input
                wire:model="current_password"
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
            />
            <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="flex flex-col gap-2">
                <label for="update_password_password" class="text-sm font-medium text-secondary">Kata Sandi Baru</label>
                <input
                    wire:model="password"
                    id="update_password_password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div class="flex flex-col gap-2">
                <label for="update_password_password_confirmation" class="text-sm font-medium text-secondary">Konfirmasi Kata Sandi Baru</label>
                <input
                    wire:model="password_confirmation"
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button
                type="submit"
                class="bg-primary text-white rounded-full py-3 px-8 text-sm font-bold hover:bg-primary-hover transition-colors cursor-pointer shadow-sm shadow-primary/20"
            >
                Perbarui Kata Sandi
            </button>

            <x-action-message class="text-sm font-medium text-success" on="password-updated">
                Tersimpan.
            </x-action-message>
        </div>
    </form>
</section>
