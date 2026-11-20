<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div class="w-full">
    <!-- Header -->
    <div class="flex flex-col items-center sm:items-start text-center sm:text-left mb-8">
        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center mb-5 text-primary">
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="16" r="1"/>
                <rect x="3" y="10" width="18" height="12" rx="2"/>
                <path d="M7 10V7a5 5 0 0 1 10 0v3"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground mb-2">Forgot password?</h1>
        <p class="text-secondary text-sm leading-relaxed">No worries. Just enter your email address and we will send you a link to reset your password.</p>
    </div>

    <!-- Session Status Alert -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Form -->
    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-4">
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
                <input wire:model="email" 
                       type="email" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com" 
                       class="w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all text-foreground placeholder:text-secondary/50 text-sm"
                       required 
                       autofocus>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                class="w-full py-3.5 bg-primary text-white rounded-xl font-bold text-sm hover:bg-primary-hover hover:shadow-lg hover:shadow-primary/20 transition-all cursor-pointer mt-2 flex items-center justify-center gap-2 group">
            <span>Email Password Reset Link</span>
            <svg class="size-4 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </button>
    </form>

    <!-- Footer -->
    <div class="mt-8 text-center">
        <p class="text-xs text-secondary">
            Remember your password? 
            <a href="{{ route('login') }}" 
               wire:navigate 
               class="font-semibold text-primary hover:text-primary-hover transition-colors">
                Back to sign in
            </a>
        </p>
    </div>
</div>
