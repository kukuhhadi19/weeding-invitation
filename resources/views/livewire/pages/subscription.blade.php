<?php

use App\Models\SubscriptionTier;
use App\Services\SubscriptionLimitService;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?SubscriptionTier $selectedTier = null;

    public bool $showCheckoutModal = false;

    public function selectPlan(int $tierId): void
    {
        $tier = SubscriptionTier::find($tierId);

        if (! $tier) {
            return;
        }

        $this->selectedTier = $tier;
        $this->showCheckoutModal = true;
    }

    public function closeCheckoutModal(): void
    {
        $this->showCheckoutModal = false;
        $this->selectedTier = null;
    }

    public function with(SubscriptionLimitService $limitService): array
    {
        $user = auth()->user();

        return [
            'tiers' => SubscriptionTier::orderBy('price_monthly')->get(),
            'quota' => $limitService->getQuotaSummary($user),
        ];
    }
}; ?>

<div>
    <x-slot name="backUrl">
        {{ auth()->user()?->isAdmin() ? route('admin.dashboard') : route('dashboard') }}
    </x-slot>

    <x-slot name="header">
        Langganan &amp; Paket
    </x-slot>

    <div class="flex flex-col gap-8">
        <!-- Redirect / Error Alert -->
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-error/10 border border-error/20 text-sm text-foreground flex items-start gap-3">
                <svg class="size-5 text-error shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" x2="12" y1="8" y2="12" />
                    <line x1="12" x2="12.01" y1="16" y2="16" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Current Subscription & Quota Overview -->
        <x-subscription-quota-card :quota="$quota" :show-manage-button="false" />

        <!-- Comparison Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-foreground">Pilih Paket Langganan</h2>
                <p class="text-sm text-secondary mt-1">Bandingkan kuota dan fitur setiap paket. Pembayaran mendukung QRIS, GoPay, Virtual Account, dan Kartu Kredit.</p>
            </div>
        </div>

        <!-- 4-Tier Comparison Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 items-stretch">
            @foreach ($tiers as $tier)
                @php
                    $isCurrent = $tier->id === $quota['tier']->id;
                    $isPopular = $tier->slug === 'premium';
                    $analyticsLabel = match ($tier->analytics_level) {
                        'basic' => 'Statistik Tamu Basic',
                        'full' => 'Analytics Tamu Lengkap',
                        'full_export' => 'Analytics Lengkap + Export',
                        default => 'Tanpa Statistik Tamu',
                    };
                    $templateLabel = match ($tier->slug) {
                        'free' => 'Template Gratis Saja',
                        'enterprise' => 'Semua Template + Custom',
                        default => 'Semua Template Premium',
                    };
                    $features = [
                        [
                            'label' => $tier->max_active_invitations === -1 ? 'Undangan Aktif Unlimited' : $tier->max_active_invitations . ' Undangan Aktif',
                            'included' => true,
                        ],
                        [
                            'label' => $tier->max_gallery_photos === -1 ? 'Galeri Foto Unlimited' : 'Maks. ' . $tier->max_gallery_photos . ' Foto Galeri',
                            'included' => true,
                        ],
                        [
                            'label' => $templateLabel,
                            'included' => true,
                        ],
                        [
                            'label' => 'Hapus Watermark Platform',
                            'included' => (bool) $tier->remove_watermark,
                        ],
                        [
                            'label' => 'Custom Slug URL Sendiri',
                            'included' => (bool) $tier->custom_slug,
                        ],
                        [
                            'label' => $analyticsLabel,
                            'included' => $tier->analytics_level !== 'none',
                        ],
                        [
                            'label' => 'Dukungan Prioritas',
                            'included' => (bool) $tier->priority_support,
                        ],
                    ];
                @endphp

                <div class="rounded-3xl p-6 flex flex-col relative transition-all {{ $isPopular ? 'bg-foreground text-white ring-2 ring-foreground shadow-xl' : ($isCurrent ? 'bg-white ring-2 ring-primary shadow-md' : 'bg-white ring-1 ring-border shadow-sm') }}">
                    @if ($isPopular)
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-primary text-white px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                            Paling Populer
                        </div>
                    @elseif ($isCurrent)
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-success text-white px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider whitespace-nowrap">
                            Paket Aktif
                        </div>
                    @endif

                    <div class="mb-5">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <h3 class="text-xl font-bold">{{ $tier->name }}</h3>
                            @if ($isCurrent && $isPopular)
                                <span class="bg-success/20 text-success text-[11px] font-bold px-2.5 py-0.5 rounded-full">Aktif</span>
                            @endif
                        </div>
                        <p class="text-xs leading-relaxed {{ $isPopular ? 'text-white/70' : 'text-secondary' }}">
                            @switch($tier->slug)
                                @case('free')
                                    Cocok untuk mencoba membuat undangan digital pertama Anda.
                                    @break
                                @case('basic')
                                    Ideal untuk calon pengantin tanpa watermark & kuota foto lebih besar.
                                    @break
                                @case('premium')
                                    Fitur lengkap dengan custom slug dan analitik tamu untuk acara spesial.
                                    @break
                                @default
                                    Solusi penuh untuk Wedding Organizer dan pengelola multi-acara.
                            @endswitch
                        </p>
                    </div>

                    <div class="mb-6 pb-6 border-b {{ $isPopular ? 'border-white/15' : 'border-border' }}">
                        @if ($tier->price_monthly === 0)
                            <span class="text-3xl font-extrabold">Rp 0</span>
                            <span class="text-xs {{ $isPopular ? 'text-white/70' : 'text-secondary' }}">/selamanya</span>
                        @else
                            <span class="text-3xl font-extrabold">Rp {{ number_format($tier->price_monthly, 0, ',', '.') }}</span>
                            <span class="text-xs {{ $isPopular ? 'text-white/70' : 'text-secondary' }}">/bulan</span>
                        @endif
                    </div>

                    <ul class="flex flex-col gap-3.5 mb-8 flex-1">
                        @foreach ($features as $feature)
                            <li class="flex items-start gap-2.5 text-sm {{ $feature['included'] ? '' : ($isPopular ? 'text-white/40' : 'text-secondary/50') }}">
                                @if ($feature['included'])
                                    <svg class="size-5 shrink-0 {{ $isPopular ? 'text-primary' : 'text-success' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                @else
                                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14" />
                                    </svg>
                                @endif
                                <span>{{ $feature['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if ($isCurrent)
                        <button
                            type="button"
                            disabled
                            class="w-full py-3 rounded-full font-bold text-sm cursor-not-allowed {{ $isPopular ? 'bg-white/15 text-white/70' : 'bg-muted text-secondary' }}"
                        >
                            Paket Anda Saat Ini
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="selectPlan({{ $tier->id }})"
                            class="w-full py-3 rounded-full font-bold text-sm transition-colors cursor-pointer {{ $isPopular ? 'bg-primary text-white hover:bg-primary-hover shadow-lg shadow-primary/30' : 'bg-white text-foreground ring-1 ring-border hover:bg-muted' }}"
                        >
                            {{ $tier->price_monthly > $quota['tier']->price_monthly ? 'Upgrade ke ' . $tier->name : 'Pilih Paket ' . $tier->name }}
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Checkout Preview Modal (Placeholder for Issue #4 Midtrans Snap) -->
    @if ($showCheckoutModal && $selectedTier)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-border flex justify-between items-center">
                    <h3 class="font-bold text-xl text-foreground">Konfirmasi Pilihan Paket</h3>
                    <button type="button" wire:click="closeCheckoutModal" class="size-8 flex items-center justify-center rounded-full hover:bg-muted cursor-pointer text-secondary">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-4">
                    <div class="p-4 rounded-2xl border-2 border-primary bg-primary/5">
                        <div class="flex items-center justify-between mb-1">
                            <h4 class="font-bold text-lg text-foreground">Paket {{ $selectedTier->name }}</h4>
                            <span class="text-sm font-bold text-primary">
                                Rp {{ number_format($selectedTier->price_monthly, 0, ',', '.') }}/bulan
                            </span>
                        </div>
                        <p class="text-secondary text-sm">
                            {{ $selectedTier->max_active_invitations === -1 ? 'Undangan Aktif Unlimited' : $selectedTier->max_active_invitations . ' Undangan Aktif' }}
                            •
                            {{ $selectedTier->max_gallery_photos === -1 ? 'Galeri Foto Unlimited' : 'Maks. ' . $selectedTier->max_gallery_photos . ' Foto' }}
                        </p>
                    </div>
                    <div class="p-4 rounded-xl bg-muted text-xs text-secondary leading-relaxed">
                        Pembayaran otomatis menggunakan <strong class="text-foreground">Midtrans Snap</strong> (QRIS, GoPay, Transfer Bank / VA, Kartu Kredit) akan diaktifkan pada tahap berikutnya (Issue #4).
                    </div>
                </div>
                <div class="p-6 border-t border-border bg-card-grey/30 flex gap-3">
                    <button type="button" wire:click="closeCheckoutModal" class="flex-1 py-3 bg-primary text-white rounded-full font-bold text-sm hover:bg-primary-hover cursor-pointer">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
