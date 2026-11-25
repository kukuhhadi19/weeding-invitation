<x-app-layout>
    <x-slot name="backUrl">
        {{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}
    </x-slot>

    <x-slot name="header">
        Pengaturan Akun
    </x-slot>

    @php
        $user = Auth::user();
        $quota = app(\App\Services\SubscriptionLimitService::class)->getQuotaSummary($user);
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="max-w-3xl mx-auto flex flex-col gap-6">
        <!-- Profile Overview Section (Adopted from templates/dashboard-account.html) -->
        <section class="bg-white rounded-2xl border border-border p-6 flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 text-center sm:text-left">
            <div class="size-20 sm:size-24 shrink-0 rounded-full bg-primary/10 text-primary font-bold text-2xl flex items-center justify-center border-4 border-muted">
                {{ $initials ?: 'U' }}
            </div>

            <div class="flex-1 min-w-0">
                <h2
                    class="text-2xl font-bold text-foreground truncate"
                    x-data="{{ json_encode(['name' => $user->name]) }}"
                    x-on:profile-updated.window="name = $event.detail.name"
                    x-text="name"
                ></h2>
                <p class="text-secondary text-sm mb-4 truncate">{{ $user->email }}</p>
                <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                    <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide">
                        Paket {{ $quota['tier']->name }}
                    </span>
                    @if ($user->created_at)
                        <span class="bg-muted text-secondary px-3 py-1 rounded-full text-xs font-medium">
                            Bergabung {{ $user->created_at->translatedFormat('M Y') }}
                        </span>
                    @endif
                </div>
            </div>

            <a
                href="#personal-details"
                class="ring-1 ring-border rounded-full py-2.5 px-6 text-sm font-medium hover:ring-primary text-foreground transition-all w-full sm:w-auto text-center"
            >
                Edit Profil
            </a>
        </section>

        <!-- Subscription & Quota Section -->
        <x-subscription-quota-card :quota="$quota" />

        <!-- Personal Details Form Section -->
        <div id="personal-details">
            <livewire:profile.update-profile-information-form />
        </div>

        <!-- Update Password Section -->
        <livewire:profile.update-password-form />

        <!-- Account Actions / Danger Zone Section -->
        <livewire:profile.delete-user-form />
    </div>
</x-app-layout>
