<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $localVerificationUrl = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        if (config('app.debug') && Auth::check() && ! Auth::user()->hasVerifiedEmail()) {
            $this->localVerificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => Auth::id(), 'hash' => sha1(Auth::user()->getEmailForVerification())]
            );
        }
    }

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $throttleKey = 'resend-verification:'.Auth::id();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            Session::flash('error', __('Terlalu banyak permintaan kirim ulang. Silakan coba lagi dalam :minutes menit.', [
                'minutes' => ceil($seconds / 60),
            ]));

            return;
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 3600);

        Auth::user()->sendEmailVerificationNotification();

        if (config('app.debug')) {
            $this->localVerificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                ['id' => Auth::id(), 'hash' => sha1(Auth::user()->getEmailForVerification())]
            );
        }

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="w-full">
    <!-- Header -->
    <div class="flex flex-col items-center sm:items-start text-center sm:text-left mb-6">
        <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center mb-5 text-primary">
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="16" x="2" y="4" rx="2"/>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                <path d="m16 19 2 2 4-4"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold tracking-tight text-foreground mb-2">Verifikasi Email</h1>
        <p class="text-secondary text-sm leading-relaxed">
            Terima kasih telah mendaftar! Anda wajib memverifikasi akun dengan membuka tautan yang kami kirimkan ke email Anda untuk dapat masuk ke dashboard.
        </p>
    </div>

    <!-- Alerts -->
    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-sm text-green-800 flex items-start gap-3">
            <svg class="size-5 text-green-600 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
            <span>Tautan verifikasi baru telah berhasil dikirim ke alamat email Anda.</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800 flex items-start gap-3">
            <svg class="size-5 text-red-600 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" x2="12" y1="8" y2="12"/>
                <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Local Preview Link Card (Dev Environment Only) -->
    @if ($localVerificationUrl)
        <div class="mb-5 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-sm">
            <div class="font-bold flex items-center gap-2">
                <svg class="size-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                </svg>
                Tautan Verifikasi Email:
            </div>
            <p class="text-xs text-blue-700 mt-1">Klik tautan di bawah ini untuk menyelesaikan proses verifikasi dan membuka Dashboard:</p>
            <a href="{{ $localVerificationUrl }}" class="inline-block mt-2.5 px-3.5 py-2 bg-primary hover:bg-primary-hover text-white font-semibold text-xs rounded-lg transition-colors shadow">
                Verifikasi Akun & Masuk Dashboard &rarr;
            </a>
        </div>
    @endif

    <!-- Actions -->
    <div class="flex flex-col gap-4">
        <button wire:click="sendVerification" 
                wire:loading.attr="disabled"
                type="button" 
                class="w-full py-3.5 px-4 bg-primary hover:bg-primary-hover disabled:opacity-60 text-white font-semibold rounded-xl shadow-lg shadow-primary/25 hover:shadow-primary/35 transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer">
            <span wire:loading.remove wire:target="sendVerification">Kirim Ulang Email Verifikasi</span>
            <span wire:loading wire:target="sendVerification" class="flex items-center gap-2">
                <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mengirim...
            </span>
            <svg wire:loading.remove wire:target="sendVerification" class="size-4 group-hover:translate-x-1 transition-transform" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
            </svg>
        </button>

        <div class="text-center mt-2">
            <button wire:click="logout" 
                    type="button" 
                    class="text-sm font-semibold text-secondary hover:text-foreground transition-colors cursor-pointer">
                Keluar (Log Out)
            </button>
        </div>
    </div>
</div>
