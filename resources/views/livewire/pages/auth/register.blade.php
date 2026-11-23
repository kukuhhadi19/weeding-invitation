<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $freeTier = \App\Models\SubscriptionTier::where('slug', 'free')->first();
        $validated['subscription_tier_id'] = $freeTier?->id;
        $validated['role'] = 'user';

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="w-full">
    <!-- Header -->
    <div class="flex flex-col items-center sm:items-start text-center sm:text-left mb-8">
        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center mb-5 text-primary">
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" x2="19" y1="8" y2="14"/>
                <line x1="22" x2="16" y1="11" y2="11"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground mb-2">Daftar Akun Baru</h1>
        <p class="text-secondary text-sm">Mulai buat undangan pernikahan digital impian Anda bersama kami.</p>
    </div>

    <!-- Form -->
    <form wire:submit="register" class="flex flex-col gap-4">
        <!-- Name Input -->
        <div class="flex flex-col gap-1.5">
            <label for="name" class="text-sm font-semibold text-foreground">Nama Lengkap</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <input wire:model="name" 
                       type="text" 
                       id="name" 
                       name="name" 
                       placeholder="cth. Sarah Jenkins" 
                       class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autofocus 
                       autocomplete="name">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Input -->
        <div class="flex flex-col gap-1.5">
            <label for="email" class="text-sm font-semibold text-foreground">Alamat Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                <input wire:model="email" 
                       type="email" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com" 
                       class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password Input -->
        <div class="flex flex-col gap-1.5" x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-semibold text-foreground">Kata Sandi</label>
                <span class="text-xs text-secondary">Min. 8 karakter</span>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <input :type="show ? 'text' : 'password'" 
                       wire:model="password" 
                       id="password" 
                       name="password" 
                       placeholder="••••••••" 
                       class="w-full pl-11 pr-11 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autocomplete="new-password">
                <button type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-secondary hover:text-foreground transition-colors cursor-pointer"
                        tabindex="-1">
                    <template x-if="!show">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </template>
                    <template x-if="show">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                            <line x1="2" x2="22" y1="2" y2="22"/>
                        </svg>
                    </template>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password Input -->
        <div class="flex flex-col gap-1.5" x-data="{ show: false }">
            <label for="password_confirmation" class="text-sm font-semibold text-foreground">Konfirmasi Kata Sandi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <input :type="show ? 'text' : 'password'" 
                       wire:model="password_confirmation" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="••••••••" 
                       class="w-full pl-11 pr-11 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autocomplete="new-password">
                <button type="button" 
                        @click="show = !show" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-secondary hover:text-foreground transition-colors cursor-pointer"
                        tabindex="-1">
                    <template x-if="!show">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </template>
                    <template x-if="show">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                            <line x1="2" x2="22" y1="2" y2="22"/>
                        </svg>
                    </template>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                wire:loading.attr="disabled"
                class="w-full py-3.5 px-4 mt-2 bg-primary hover:bg-primary-hover disabled:opacity-60 text-white font-semibold rounded-xl shadow-lg shadow-primary/25 hover:shadow-primary/35 transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer">
            <span wire:loading.remove wire:target="register">Daftar Sekarang</span>
            <span wire:loading wire:target="register" class="flex items-center gap-2">
                <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            </span>
            <svg wire:loading.remove wire:target="register" class="size-4 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </button>

        <!-- Terms Notice -->
        <p class="text-xs text-center text-secondary/70 mt-1">
            Dengan mendaftar, Anda menyetujui Ketentuan Layanan & Kebijakan Privasi kami.
        </p>

        <!-- Footer / Link to Login -->
        <div class="mt-4 text-center">
            <p class="text-sm text-secondary">
                Sudah punya akun? 
                <a href="{{ route('login') }}" 
                   wire:navigate 
                   class="font-semibold text-primary hover:text-primary-hover transition-colors">
                    Masuk ke Akun
                </a>
            </p>
        </div>
    </form>
</div>
