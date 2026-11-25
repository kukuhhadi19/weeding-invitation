<x-app-layout>
    <x-slot name="header">
        Akun
    </x-slot>

    <x-slot name="subtitle">
        Kelola profil, keamanan akun, dan langganan Anda dari satu tempat.
    </x-slot>

    @php
        $user = Auth::user();
        $quota = app(\App\Services\SubscriptionLimitService::class)->getQuotaSummary($user);
        $tiers = \App\Models\SubscriptionTier::orderBy('price_monthly')->get();
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="max-w-4xl mx-auto flex flex-col gap-6">
        <!-- Profile Overview Section -->
        <section class="bg-white rounded-2xl border border-border p-6 flex flex-col sm:flex-row items-center sm:items-start gap-5 sm:gap-6 text-center sm:text-left">
            <div class="relative shrink-0">
                <div class="size-20 sm:size-24 rounded-full bg-primary/10 text-primary font-bold text-2xl flex items-center justify-center border-4 border-muted">
                    {{ $initials ?: 'U' }}
                </div>
                <span class="absolute bottom-0 right-0 size-8 bg-white rounded-full border border-border flex items-center justify-center text-secondary shadow-sm" aria-hidden="true">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                        <circle cx="12" cy="13" r="3" />
                    </svg>
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    <h2
                        class="text-2xl font-bold text-foreground truncate"
                        x-data="{{ json_encode(['name' => $user->name]) }}"
                        x-on:profile-updated.window="name = $event.detail.name"
                        x-text="name"
                    ></h2>
                    <span class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider">
                        <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                        </svg>
                        {{ strtoupper($user->role ?? 'user') }}
                    </span>
                </div>

                <p class="text-secondary text-sm mt-1 mb-4 truncate">{{ $user->email }}</p>

                <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                    <span class="bg-success/10 text-success px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                        Paket {{ $quota['tier']->name }} Aktif
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
                class="ring-1 ring-border rounded-full py-2.5 px-6 text-sm font-semibold hover:ring-primary text-foreground transition-all w-full sm:w-auto text-center shrink-0"
            >
                Edit Profil
            </a>
        </section>

        <!-- Subscription & Quota 2-Column Section -->
        <x-subscription-quota-card :quota="$quota" />

        <!-- Subscription & Billing + 4-Tier Overview Cards -->
        <section class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border flex justify-between items-center bg-card-grey/50">
                <div class="flex items-center gap-2.5">
                    <svg class="size-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2" />
                        <line x1="2" x2="22" y1="10" y2="10" />
                    </svg>
                    <h3 class="font-bold text-base sm:text-lg text-foreground">Langganan &amp; Penagihan</h3>
                </div>
                <a href="{{ route('subscription.index') }}" wire:navigate class="text-xs font-bold text-primary hover:underline">
                    Bandingkan Detail &rarr;
                </a>
            </div>

            <div class="p-6 flex flex-col gap-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    @foreach ($tiers as $tier)
                        @php
                            $isCurrent = $tier->id === $quota['tier']->id;
                        @endphp
                        <a
                            href="{{ route('subscription.index') }}"
                            wire:navigate
                            class="rounded-2xl p-4 border transition-all flex flex-col gap-2 {{ $isCurrent ? 'border-primary ring-1 ring-primary bg-primary/[0.03]' : 'border-border hover:border-primary/50 hover:bg-card-grey/30' }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-base text-foreground">{{ $tier->name }}</span>
                                    @if ($isCurrent)
                                        <span class="bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">
                                            Aktif
                                        </span>
                                    @endif
                                </div>
                                <svg class="size-4 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z" />
                                </svg>
                            </div>

                            <p class="text-sm font-bold text-foreground">
                                {{ $tier->price_monthly === 0 ? 'Rp 0' : 'Rp ' . number_format($tier->price_monthly, 0, ',', '.') }}
                                <span class="text-xs font-normal text-secondary">{{ $tier->price_monthly === 0 ? '/selamanya' : '/bulan' }}</span>
                            </p>

                            <p class="text-xs text-secondary">
                                {{ $tier->max_active_invitations === -1 ? 'Undangan Unlimited' : $tier->max_active_invitations . ' Undangan Aktif' }}
                                &bull;
                                {{ $tier->max_gallery_photos === -1 ? 'Foto Unlimited' : $tier->max_gallery_photos . ' Foto' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

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
