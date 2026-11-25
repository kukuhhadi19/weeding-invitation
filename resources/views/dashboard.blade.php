<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    @php
        $user = Auth::user();
        $quota = app(\App\Services\SubscriptionLimitService::class)->getQuotaSummary($user);
    @endphp

    <div class="flex flex-col gap-6" x-data="{ wizardInfoOpen: false }">
        <!-- Hero Greeting Card -->
        <section class="bg-foreground rounded-3xl p-6 md:p-8 text-white relative overflow-hidden shadow-xl">
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 text-white text-xs font-semibold rounded-full mb-4 backdrop-blur-sm border border-white/20">
                        <span class="size-1.5 rounded-full bg-success"></span>
                        Paket {{ $quota['tier']->name }}
                    </span>
                    <h2 class="text-2xl md:text-3xl font-bold leading-tight mb-2">
                        Selamat datang, {{ $user->name }}!
                    </h2>
                    <p class="text-white/75 text-sm md:text-base leading-relaxed">
                        Kelola undangan pernikahan digital Anda, pantau kuota paket aktif, dan pilih desain terbaik untuk hari bahagia Anda.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    @if ($quota['limit_reached'])
                        <button
                            type="button"
                            disabled
                            aria-disabled="true"
                            class="px-6 py-3.5 rounded-full bg-white/15 text-white/55 font-bold text-sm cursor-not-allowed flex items-center gap-2 border border-white/10"
                        >
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            Buat Undangan
                        </button>

                        <a
                            href="{{ route('subscription.index') }}"
                            wire:navigate
                            class="px-6 py-3.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors shadow-lg shadow-primary/30 flex items-center gap-2"
                        >
                            Upgrade Paket &rarr;
                        </a>
                    @else
                        <button
                            type="button"
                            @click="wizardInfoOpen = true"
                            class="px-6 py-3.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors shadow-lg shadow-primary/30 cursor-pointer flex items-center gap-2"
                        >
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                            Buat Undangan
                        </button>
                    @endif
                </div>
            </div>

            <!-- Decorative Gradient -->
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-primary/20 to-transparent pointer-events-none"></div>
        </section>

        <!-- Quota Reached Warning Banner -->
        @if ($quota['limit_reached'])
            <div class="bg-error/10 border border-error/25 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <svg class="size-6 text-error shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </svg>
                    <div>
                        <h3 class="font-bold text-foreground text-base">Batas Undangan Aktif Tercapai</h3>
                        <p class="text-sm text-secondary mt-0.5">
                            Paket {{ $quota['tier']->name }} Anda hanya mendukung maksimal {{ $quota['limit'] }} undangan aktif. Upgrade paket Anda untuk membuat undangan baru.
                        </p>
                    </div>
                </div>
                <a
                    href="{{ route('subscription.index') }}"
                    wire:navigate
                    class="shrink-0 px-5 py-2.5 rounded-full bg-error text-white font-bold text-sm hover:opacity-90 transition-opacity text-center"
                >
                    Lihat Paket Upgrade
                </a>
            </div>
        @endif

        <!-- Quick Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-sm font-medium text-secondary">Undangan Aktif</p>
                <p class="mt-2 text-3xl font-bold text-foreground">
                    {{ $quota['used'] }} / {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['limit'] }}
                </p>
                <p class="mt-1.5 text-xs text-secondary">Maksimum undangan aktif sesuai paket saat ini.</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-sm font-medium text-secondary">Kuota Galeri Foto</p>
                <p class="mt-2 text-3xl font-bold text-foreground">
                    {{ $quota['max_gallery_photos'] === -1 ? 'Unlimited' : 'Maks ' . $quota['max_gallery_photos'] . ' Foto' }}
                </p>
                <p class="mt-1.5 text-xs text-secondary">Maksimum upload foto WebP per undangan.</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-sm font-medium text-secondary">Fitur Paket {{ $quota['tier']->name }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $quota['remove_watermark'] ? 'bg-success/10 text-success' : 'bg-muted text-secondary' }}">
                        {{ $quota['remove_watermark'] ? 'Tanpa Watermark' : 'Watermark Aktif' }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $quota['custom_slug'] ? 'bg-primary/10 text-primary' : 'bg-muted text-secondary' }}">
                        {{ $quota['custom_slug'] ? 'Custom Slug Aktif' : 'Slug Otomatis' }}
                    </span>
                </div>
                <p class="mt-2 text-xs text-secondary">
                    <a href="{{ route('subscription.index') }}" wire:navigate class="text-primary font-semibold hover:underline">Bandingkan seluruh paket &rarr;</a>
                </p>
            </div>
        </div>

        <!-- Subscription & Quota Card (Adopted from templates/dashboard-account.html) -->
        <x-subscription-quota-card :quota="$quota" />

        <!-- Invitation Wizard Upcoming Modal -->
        <div
            x-show="wizardInfoOpen"
            x-transition.opacity
            @click.self="wizardInfoOpen = false"
            class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-xl text-foreground">Buat Undangan Baru</h3>
                    <button type="button" @click="wizardInfoOpen = false" class="size-8 rounded-full hover:bg-muted flex items-center justify-center text-secondary cursor-pointer">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
                <p class="text-sm text-secondary leading-relaxed mb-6">
                    Kuota undangan Anda masih tersedia ({{ $quota['is_unlimited'] ? 'Unlimited' : $quota['remaining'] . ' slot tersisa' }}). Katalog template dan wizard pembuatan undangan akan aktif setelah Issue #7 &amp; Issue #8 diimplementasikan.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="wizardInfoOpen = false" class="px-6 py-2.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm cursor-pointer">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
