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
@endphp

<section class="bg-white rounded-2xl border border-border overflow-hidden">
    <div class="p-5 md:p-6 border-b border-border flex justify-between items-center bg-card-grey/50">
        <div class="flex items-center gap-2.5">
            <svg class="size-5 text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
            </svg>
            <h3 class="font-bold text-lg text-foreground">Langganan & Kuota</h3>
        </div>
        <span class="bg-success/10 text-success text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider flex items-center gap-1.5">
            <span class="size-1.5 rounded-full bg-success"></span> Aktif
        </span>
    </div>

    <div class="p-5 md:p-6 flex flex-col gap-6">
        <!-- Plan Details -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-sm text-secondary font-medium mb-1">Paket Saat Ini</p>
                <div class="flex items-baseline gap-2 flex-wrap">
                    <h4 class="text-3xl font-bold text-foreground">{{ $tier->name }}</h4>
                    <p class="text-secondary font-medium">
                        / {{ $formattedPrice }}@if ($tier->price_monthly > 0)<span class="text-sm">/bulan</span>@endif
                    </p>
                </div>
                <p class="text-sm text-secondary mt-1.5 flex items-center gap-1.5">
                    <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 2v4" />
                        <path d="M16 2v4" />
                        <rect width="18" height="18" x="3" y="4" rx="2" />
                        <path d="M3 10h18" />
                    </svg>
                    @if ($tier->price_monthly > 0 && $user?->subscription_expires_at)
                        Aktif hingga {{ $user->subscription_expires_at->translatedFormat('d M Y') }}
                    @else
                        Berlaku selamanya (tanpa biaya bulanan)
                    @endif
                </p>
            </div>

            @if ($showManageButton)
                <a
                    href="{{ route('subscription.index') }}"
                    wire:navigate
                    class="bg-primary text-white rounded-full py-3 px-6 text-sm font-bold hover:bg-primary-hover transition-colors w-full sm:w-auto text-center cursor-pointer shadow-sm shadow-primary/20"
                >
                    Kelola Paket
                </a>
            @endif
        </div>

        <div class="h-px w-full bg-border"></div>

        <!-- Quota Progress -->
        <div>
            <div class="flex justify-between items-end mb-3">
                <div>
                    <p class="text-sm text-secondary font-medium mb-1">Kuota Undangan Aktif</p>
                    <p class="font-bold text-foreground flex items-baseline gap-1">
                        <span class="text-3xl">{{ $quota['used'] }}</span>
                        <span class="text-secondary font-medium text-lg">
                            / {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['limit'] }}
                        </span>
                        <span class="text-secondary text-sm font-normal ml-1">digunakan</span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold {{ $quota['limit_reached'] ? 'text-error' : 'text-primary' }} mb-1">
                        {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['usage_percentage'] . '%' }}
                    </p>
                </div>
            </div>

            @if (! $quota['is_unlimited'])
                <div class="h-3 w-full bg-muted rounded-full overflow-hidden">
                    <div
                        class="h-full {{ $quota['limit_reached'] ? 'bg-error' : 'bg-primary' }} rounded-full transition-all duration-500 ease-out"
                        style="width: {{ $quota['usage_percentage'] }}%;"
                    ></div>
                </div>
            @endif

            @if ($quota['limit_reached'])
                <div class="flex items-start gap-2.5 mt-4 bg-error/10 p-3.5 rounded-xl border border-error/20">
                    <svg class="size-5 text-error shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" x2="12" y1="8" y2="12" />
                        <line x1="12" x2="12.01" y1="16" y2="16" />
                    </svg>
                    <p class="text-sm text-foreground leading-relaxed">
                        Kuota undangan aktif Anda sudah penuh ({{ $quota['used'] }}/{{ $quota['limit'] }}).
                        <a href="{{ route('subscription.index') }}" wire:navigate class="text-primary font-semibold hover:underline">Upgrade paket sekarang</a> untuk membuat lebih banyak undangan.
                    </p>
                </div>
            @else
                <div class="flex items-start gap-2.5 mt-4 bg-primary/5 p-3.5 rounded-xl border border-primary/10">
                    <svg class="size-5 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 16v-4" />
                        <path d="M12 8h.01" />
                    </svg>
                    <p class="text-sm text-secondary leading-relaxed">
                        @if ($quota['is_unlimited'])
                            Anda memiliki kuota <strong class="text-foreground">unlimited undangan aktif</strong> dan galeri foto tanpa batas pada paket Enterprise.
                        @else
                            Anda masih bisa membuat <strong class="text-foreground">{{ $quota['remaining'] }} undangan aktif</strong> lagi (maks. {{ $quota['max_gallery_photos'] }} foto per undangan).
                            @if ($showManageButton)
                                Butuh kuota lebih besar? <a href="{{ route('subscription.index') }}" wire:navigate class="text-primary font-semibold hover:underline">Lihat pilihan paket</a>.
                            @endif
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</section>
