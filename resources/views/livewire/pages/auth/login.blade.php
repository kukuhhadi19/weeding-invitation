<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('verification.notice', absolute: false), navigate: true);

            return;
        }

        $targetRoute = ($user && $user->isAdmin()) 
            ? route('admin.dashboard', absolute: false) 
            : route('dashboard', absolute: false);

        $this->redirectIntended(default: $targetRoute, navigate: true);
    }
}; ?>

<div class="w-full">
    <!-- Header -->
    <div class="flex flex-col items-center sm:items-start text-center sm:text-left mb-8">
        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center mb-5 text-primary">
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z"/>
                <path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground mb-2">Welcome back</h1>
        <p class="text-secondary text-sm">Please enter your details to sign in to your account.</p>
    </div>

    <!-- Session Status Alert -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Form -->
    <form wire:submit="login" class="flex flex-col gap-4">
        <!-- Email Input -->
        <div class="flex flex-col gap-1.5">
            <label for="email" class="text-sm font-semibold text-foreground">Email address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                </div>
                <input wire:model="form.email" 
                       type="email" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com" 
                       class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autofocus 
                       autocomplete="username">
            </div>
            <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
        </div>

        <!-- Password Input -->
        <div class="flex flex-col gap-1.5" x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-semibold text-foreground">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" 
                       wire:navigate 
                       class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-secondary">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <input :type="show ? 'text' : 'password'" 
                       wire:model="form.password" 
                       id="password" 
                       name="password" 
                       placeholder="••••••••" 
                       class="w-full pl-11 pr-11 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autocomplete="current-password">
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
            <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center gap-2.5 mt-1">
            <input wire:model="form.remember" 
                   id="remember" 
                   type="checkbox" 
                   class="custom-checkbox">
            <label for="remember" class="text-xs font-medium text-secondary cursor-pointer select-none">
                Remember me for 30 days
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full py-3.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary-hover hover:shadow-lg hover:shadow-primary/20 transition-all cursor-pointer mt-2 flex items-center justify-center gap-2 group">
            <span>Sign In</span>
            <svg class="size-4 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </button>
    </form>

    <!-- Footer -->
    <div class="mt-8 text-center">
        <p class="text-xs text-secondary">
            Don't have an account? 
            <a href="{{ route('register') }}" 
               wire:navigate 
               class="font-semibold text-primary hover:text-primary-hover transition-colors">
                Sign up for free
            </a>
        </p>
    </div>
</div>
