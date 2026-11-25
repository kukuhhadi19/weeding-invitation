<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Jelajahi desain undangan pernikahan digital, pantau kuota, dan kelola acara Anda.
    </x-slot>

    @php
        $user = Auth::user();
        $limitService = app(\App\Services\SubscriptionLimitService::class);
        $quota = $limitService->getQuotaSummary($user);
        $canUsePremiumTemplates = $limitService->canUseTemplate($user, true);

        $showcaseTemplates = [
            [
                'id' => 'classic-floral',
                'title' => 'Classic Floral Ivory',
                'author' => 'Studio Senja',
                'category' => 'floral',
                'categoryLabel' => 'Floral Elegan',
                'isPremium' => false,
                'palette' => 'from-rose-100 via-amber-50 to-white',
                'accent' => 'text-rose-700',
                'badgeBg' => 'bg-success/10 text-success',
                'description' => 'Desain floral klasik bernuansa lembut dengan tipografi elegan untuk akad dan resepsi.',
            ],
            [
                'id' => 'javanese-heritage',
                'title' => 'Kirana Adat Jawa',
                'author' => 'Karya Nusantara',
                'category' => 'adat',
                'categoryLabel' => 'Adat & Tradisional',
                'isPremium' => true,
                'palette' => 'from-amber-900 via-stone-900 to-stone-800',
                'accent' => 'text-amber-300',
                'badgeBg' => 'bg-primary/10 text-primary',
                'darkCard' => true,
                'description' => 'Sentuhan ornamen klasik Jawa dengan palet emas hangat dan struktur acara adat lengkap.',
            ],
            [
                'id' => 'nordic-minimal',
                'title' => 'Modern Minimalist',
                'author' => 'Rupa Studio',
                'category' => 'minimalis',
                'categoryLabel' => 'Modern Minimalis',
                'isPremium' => false,
                'palette' => 'from-slate-100 via-blue-50 to-white',
                'accent' => 'text-foreground',
                'badgeBg' => 'bg-success/10 text-success',
                'description' => 'Tata letak modern minimalis dengan fokus jadwal acara, peta lokasi, dan galeri bersih.',
            ],
            [
                'id' => 'rustic-garden',
                'title' => 'Rustic Botanical',
                'author' => 'Daun & Pena',
                'category' => 'rustic',
                'categoryLabel' => 'Rustic & Alam',
                'isPremium' => true,
                'palette' => 'from-emerald-100 via-stone-100 to-amber-50',
                'accent' => 'text-emerald-800',
                'badgeBg' => 'bg-primary/10 text-primary',
                'description' => 'Nuansa taman outdoor bernuansa botanical hijau sage yang hangat untuk pesta kebun.',
            ],
        ];
    @endphp

    <div
        class="flex flex-col gap-8"
        x-data="{
            wizardInfoOpen: false,
            activeCategory: 'all',
            searchQuery: '',
            previewItem: null,
            canUsePremium: {{ $canUsePremiumTemplates ? 'true' : 'false' }},
            limitReached: {{ $quota['limit_reached'] ? 'true' : 'false' }},
            matchesFilter(category, title, author) {
                const catMatch = this.activeCategory === 'all' || this.activeCategory === category;
                const q = this.searchQuery.trim().toLowerCase();
                const searchMatch = q === '' || title.toLowerCase().includes(q) || author.toLowerCase().includes(q) || category.toLowerCase().includes(q);
                return catMatch && searchMatch;
            },
            openPreview(item) {
                this.previewItem = item;
            }
        }"
    >
        <!-- Dark Hero Banner (Adopted from templates/dashboard-template.html) -->
        <section class="bg-foreground rounded-3xl p-8 md:p-10 flex items-center justify-between relative overflow-hidden shadow-xl text-white">
            <div class="relative z-10 max-w-xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 text-white text-xs font-semibold rounded-full mb-4 backdrop-blur-sm border border-white/20">
                    <span class="size-1.5 rounded-full {{ $quota['limit_reached'] ? 'bg-error' : 'bg-success' }}"></span>
                    Paket {{ $quota['tier']->name }} &bull; {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['used'] . '/' . $quota['limit'] . ' Undangan' }}
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4 leading-tight">
                    Temukan Desain Sempurna untuk Hari Bahagia Anda
                </h2>
                <p class="text-white/75 mb-8 text-base md:text-lg leading-relaxed">
                    Selamat datang, {{ $user->name }}! Jelajahi koleksi template undangan pernikahan digital kami, pantau kuota paket aktif, dan bagikan ke tamu dalam hitungan menit.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($quota['limit_reached'])
                        <button
                            type="button"
                            disabled
                            aria-disabled="true"
                            class="px-7 py-3.5 rounded-full bg-white/15 text-white/60 font-bold text-sm cursor-not-allowed flex items-center gap-2 border border-white/10"
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
                            class="px-8 py-3.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors shadow-lg shadow-primary/30 inline-flex items-center gap-2"
                        >
                            Upgrade Paket &rarr;
                        </a>
                    @else
                        <button
                            type="button"
                            @click="wizardInfoOpen = true"
                            class="px-8 py-3.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors shadow-lg shadow-primary/30 cursor-pointer inline-flex items-center gap-2"
                        >
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                            Buat Undangan
                        </button>

                        <a
                            href="{{ route('subscription.index') }}"
                            wire:navigate
                            class="px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/20 transition-colors"
                        >
                            Lihat Paket
                        </a>
                    @endif
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="hidden md:block absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-primary/20 to-transparent pointer-events-none"></div>
            <svg class="hidden md:block absolute -right-10 -top-10 size-64 text-white/5 rotate-12 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z" />
            </svg>
            <svg class="hidden md:block absolute right-20 bottom-10 size-24 text-primary/20 -rotate-12 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
            </svg>
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

        <!-- Template Showcase Header + Category Filter Pills -->
        <section class="flex flex-col gap-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-xl text-foreground">Pilihan Desain Undangan</h2>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5">Pratinjau katalog awal desain (katalog database penuh aktif di Issue #6 &amp; #7).</p>
                </div>

                <div class="relative w-full md:w-72">
                    <svg class="size-4 text-secondary absolute left-4 top-1/2 -translate-y-1/2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input
                        type="search"
                        x-model="searchQuery"
                        placeholder="Cari tema atau gaya desain..."
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-border rounded-full text-sm text-foreground placeholder:text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                    />
                </div>
            </div>

            <!-- Horizontal Category Pills -->
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex gap-3 min-w-max pb-1">
                    @foreach ([
                        'all' => 'Semua Desain',
                        'floral' => 'Floral Elegan',
                        'adat' => 'Adat & Tradisional',
                        'minimalis' => 'Modern Minimalis',
                        'rustic' => 'Rustic & Alam',
                    ] as $key => $label)
                        <button
                            type="button"
                            @click="activeCategory = '{{ $key }}'"
                            :class="activeCategory === '{{ $key }}'
                                ? 'bg-foreground text-white shadow-sm'
                                : 'bg-white border border-border text-secondary hover:border-primary hover:text-primary'"
                            class="px-5 py-2.5 rounded-full font-medium text-sm transition-all cursor-pointer"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 4-Column Template Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($showcaseTemplates as $tpl)
                    <article
                        x-show="matchesFilter('{{ $tpl['category'] }}', '{{ $tpl['title'] }}', '{{ $tpl['author'] }}')"
                        x-transition.opacity
                        class="group flex flex-col bg-white rounded-2xl border border-border overflow-hidden hover:shadow-xl hover:shadow-black/5 transition-all duration-300"
                    >
                        <div
                            @click="openPreview({{ json_encode($tpl) }})"
                            class="relative aspect-[3/4] bg-gradient-to-br {{ $tpl['palette'] }} p-5 flex flex-col justify-between overflow-hidden cursor-pointer"
                        >
                            <!-- Top Badges -->
                            <div class="flex items-center justify-between gap-2 relative z-10">
                                <span class="bg-white/90 backdrop-blur-sm text-foreground text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm">
                                    {{ $tpl['categoryLabel'] }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $tpl['isPremium'] ? 'bg-primary text-white' : 'bg-white/90 text-success' }}">
                                    {{ $tpl['isPremium'] ? 'Premium' : 'Gratis' }}
                                </span>
                            </div>

                            <!-- Editorial Mockup Card Frame -->
                            <div class="my-auto mx-auto w-full max-w-[175px] aspect-[4/5] rounded-2xl border {{ ! empty($tpl['darkCard']) ? 'border-amber-300/30 bg-black/25' : 'border-black/10 bg-white/75' }} backdrop-blur-sm p-4 flex flex-col items-center justify-center text-center shadow-sm group-hover:scale-105 transition-transform duration-500">
                                <p class="text-[10px] uppercase tracking-[0.2em] {{ ! empty($tpl['darkCard']) ? 'text-amber-200/80' : 'text-secondary' }}">The Wedding Of</p>
                                <p class="mt-2 text-lg font-bold leading-snug {{ $tpl['accent'] }}">Dina &amp; Raka</p>
                                <div class="w-8 h-px {{ ! empty($tpl['darkCard']) ? 'bg-amber-300/40' : 'bg-border' }} my-2.5"></div>
                                <p class="text-[11px] {{ ! empty($tpl['darkCard']) ? 'text-white/70' : 'text-secondary' }}">Minggu, 18 Okt 2026</p>
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center z-20">
                                <span class="px-5 py-2.5 bg-white rounded-full font-semibold text-sm text-foreground hover:bg-primary hover:text-white transition-colors shadow-lg">
                                    Pratinjau
                                </span>
                            </div>
                        </div>

                        <div class="p-5 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-bold text-foreground truncate text-base">{{ $tpl['title'] }}</h3>
                                <p class="text-xs text-secondary mt-0.5 truncate">Oleh {{ $tpl['author'] }}</p>
                            </div>
                            <button
                                type="button"
                                @click="openPreview({{ json_encode($tpl) }})"
                                class="size-9 rounded-full bg-muted hover:bg-primary hover:text-white text-secondary flex items-center justify-center transition-colors shrink-0 cursor-pointer"
                                aria-label="Lihat detail {{ $tpl['title'] }}"
                            >
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <!-- Subscription & Quota 2-Column Section -->
        <x-subscription-quota-card :quota="$quota" />

        <!-- Template Detail Preview Modal -->
        <div
            x-show="previewItem !== null"
            x-transition.opacity
            @click.self="previewItem = null"
            class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 md:p-8 backdrop-blur-sm"
            style="display: none;"
        >
            <div class="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col sm:flex-row relative" x-if="previewItem">
                <button
                    type="button"
                    @click="previewItem = null"
                    aria-label="Tutup pratinjau"
                    class="absolute top-4 right-4 z-10 size-9 bg-white/90 rounded-full flex items-center justify-center text-foreground hover:bg-muted transition-colors cursor-pointer shadow-sm"
                >
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>

                <div class="w-full sm:w-5/12 bg-muted p-6 flex items-center justify-center">
                    <div class="w-full max-w-[200px] aspect-[3/4] rounded-2xl bg-white border border-border p-5 flex flex-col items-center justify-center text-center shadow-md">
                        <span class="px-2.5 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-wider" x-text="previewItem?.categoryLabel"></span>
                        <p class="mt-4 text-xs text-secondary uppercase tracking-widest">Undangan</p>
                        <p class="mt-2 text-lg font-bold text-foreground" x-text="previewItem?.title"></p>
                    </div>
                </div>

                <div class="w-full sm:w-7/12 p-6 md:p-8 flex flex-col">
                    <span class="inline-block w-fit px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary mb-3" x-text="previewItem?.isPremium ? 'Template Premium' : 'Template Gratis'"></span>
                    <h3 class="text-2xl font-bold text-foreground mb-1" x-text="previewItem?.title"></h3>
                    <p class="text-sm text-secondary mb-4" x-text="'Oleh ' + (previewItem?.author || '')"></p>
                    <p class="text-sm text-secondary leading-relaxed mb-6" x-text="previewItem?.description"></p>

                    <div class="mt-auto flex flex-col gap-2.5">
                        <template x-if="limitReached">
                            <a href="{{ route('subscription.index') }}" wire:navigate class="w-full py-3 bg-error text-white rounded-full font-bold text-sm text-center hover:opacity-90 transition-opacity">
                                Kuota Penuh — Upgrade Paket
                            </a>
                        </template>
                        <template x-if="!limitReached && previewItem?.isPremium && !canUsePremium">
                            <a href="{{ route('subscription.index') }}" wire:navigate class="w-full py-3 bg-primary text-white rounded-full font-bold text-sm text-center hover:bg-primary-hover transition-colors">
                                Upgrade untuk Akses Template Premium
                            </a>
                        </template>
                        <template x-if="!limitReached && (!previewItem?.isPremium || canUsePremium)">
                            <button type="button" @click="previewItem = null; wizardInfoOpen = true" class="w-full py-3 bg-primary text-white rounded-full font-bold text-sm hover:bg-primary-hover transition-colors cursor-pointer">
                                Gunakan Desain Ini
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invitation Wizard Upcoming Modal -->
        <div
            x-show="wizardInfoOpen"
            x-transition.opacity
            @click.self="wizardInfoOpen = false"
            class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm"
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
                    Kuota undangan Anda masih tersedia ({{ $quota['is_unlimited'] ? 'Unlimited' : $quota['remaining'] . ' slot tersisa' }}). Katalog template dinamis dan wizard pembuatan undangan akan aktif setelah Issue #7 &amp; Issue #8 diimplementasikan.
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
