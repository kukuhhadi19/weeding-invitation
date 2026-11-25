<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="p-5 md:p-6 border-b border-border">
        <h3 class="font-bold text-lg text-foreground">Informasi Profil</h3>
        <p class="text-sm text-secondary mt-1">Perbarui nama tampilan dan alamat email akun Anda.</p>
    </div>

    <form wire:submit="updateProfileInformation" class="p-5 md:p-6 flex flex-col gap-5">
        <div class="flex flex-col gap-2">
            <label for="name" class="text-sm font-medium text-secondary">Nama Lengkap</label>
            <input
                wire:model="name"
                id="name"
                name="name"
                type="text"
                required
                autofocus
                autocomplete="name"
                class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
            />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div class="flex flex-col gap-2">
            <label for="email" class="text-sm font-medium text-secondary">Alamat Email</label>
            <input
                wire:model="email"
                id="email"
                name="email"
                type="email"
                required
                autocomplete="username"
                class="w-full bg-muted/50 border border-border rounded-xl px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
            />
            <x-input-error class="mt-1" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2 p-3.5 rounded-xl bg-warning/15 border border-warning/30 text-sm text-foreground">
                    <p>
                        Alamat email Anda belum terverifikasi.
                        <button
                            wire:click.prevent="sendVerification"
                            type="button"
                            class="underline font-semibold text-primary hover:text-primary-hover cursor-pointer"
                        >
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-success">
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button
                type="submit"
                class="bg-primary text-white rounded-full py-3 px-8 text-sm font-bold hover:bg-primary-hover transition-colors cursor-pointer shadow-sm shadow-primary/20"
            >
                Simpan Perubahan
            </button>

            <x-action-message class="text-sm font-medium text-success" on="profile-updated">
                Tersimpan.
            </x-action-message>
        </div>
    </form>
</section>
