@props([
    'quota',
    'showManageButton' => true,
])

@php
    $tier = $quota['tier'];
    $formattedPrice = $tier->price_monthly === 0
        ? 'Gratis'
        : 'Rp ' . number_format($tier->price_monthly, 0, ',', '.');
    $user = auth()->user();
    $limitLabel = $quota['is_unlimited'] ? 'UNLIMITED' : 'BATAS ' . $quota['limit'];
@endphp

<section class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="px-6 py-4 border-b border-border flex justify-between items-center bg-card-grey/50">
        <div class="flex items-center gap-2.5">
            <svg class="size-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
            </svg>
            <h3 class="font-bold text-base sm:text-lg text-foreground">Langganan & Kuota</h3>
        </div>
        <span class="bg-success/10 text-success text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1.5">
            <span class="size-1.5 rounded-full bg-success"></span> Aktif
        </span>
    </div>

    <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
        <!-- Left Column: Plan Details -->
        <div class="lg:col-span-7 flex flex-col items-start gap-2">
            <p class="text-xs font-medium text-secondary">Paket Saat Ini</p>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h4 class="text-2xl sm:text-3xl font-bold text-foreground">{{ $tier->name }}</h4>
                <span class="bg-primary/10 text-primary text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wide">
                    {{ $limitLabel }}
                </span>
            </div>
            <p class="text-sm text-secondary mt-0.5">
                @if ($tier->price_monthly > 0 && $user?->subscription_expires_at)
                    Paket aktif hingga {{ $user->subscription_expires_at->translatedFormat('d M Y') }}.
                @else
                    Paket aktif selamanya tanpa batas tanggal berakhir.
                @endif
            </p>
            <p class="text-xs text-secondary">
                {{ $formattedPrice }}{{ $tier->price_monthly > 0 ? '/bulan' : '' }} terhubung dengan siklus langganan akun Anda. Maks. {{ $quota['max_gallery_photos'] === -1 ? 'unlimited' : $quota['max_gallery_photos'] }} foto galeri per undangan.
            </p>

            @if ($showManageButton)
                <a
                    href="{{ route('subscription.index') }}"
                    wire:navigate
                    class="mt-3 inline-flex items-center gap-2 bg-primary text-white rounded-full py-2.5 px-5 text-xs sm:text-sm font-bold hover:bg-primary-hover transition-colors shadow-sm shadow-primary/20"
                >
                    Kelola Paket &rarr;
                </a>
            @endif
        </div>

        <!-- Right Column: Nested Quota Box -->
        <div class="lg:col-span-5 rounded-2xl border border-border bg-card-grey/40 p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-secondary">Undangan Aktif</span>
                <span class="text-xs font-bold {{ $quota['limit_reached'] ? 'text-error' : 'text-primary' }}">
                    {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['usage_percentage'] . '%' }}
                </span>
            </div>

            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-foreground">{{ $quota['used'] }}</span>
                <span class="text-sm font-medium text-secondary">
                    / {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['limit'] }}
                </span>
            </div>

            @if (! $quota['is_unlimited'])
                <div class="h-2 w-full bg-muted rounded-full overflow-hidden">
                    <div
                        class="h-full {{ $quota['limit_reached'] ? 'bg-error' : 'bg-primary' }} rounded-full transition-all duration-500 ease-out"
                        style="width: {{ $quota['usage_percentage'] }}%;"
                    ></div>
                </div>
            @endif

            @if ($quota['limit_reached'])
                <div class="flex items-start gap-2 bg-error/10 border border-error/20 rounded-xl p-3 text-xs text-foreground leading-relaxed">
                    <svg class="size-4 text-error shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" x2="12" y1="8" y2="12" />
                        <line x1="12" x2="12.01" y1="16" y2="16" />
                    </svg>
                    <span>
                        Kuota penuh ({{ $quota['used'] }}/{{ $quota['limit'] }}).
                        <a href="{{ route('subscription.index') }}" wire:navigate class="text-primary font-semibold hover:underline">Upgrade paket</a>.
                    </span>
                </div>
            @else
                <div class="flex items-start gap-2 bg-primary/5 border border-primary/10 rounded-xl p-3 text-xs text-secondary leading-relaxed">
                    <svg class="size-4 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <span>
                        @if ($quota['is_unlimited'])
                            Anda memiliki slot undangan aktif tanpa batas.
                        @else
                            Anda masih memiliki <strong class="text-foreground">{{ $quota['remaining'] }} slot</strong> undangan aktif tersisa.
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </div>
</section>
