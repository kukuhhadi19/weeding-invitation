<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Wedding Invitation') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans bg-muted min-h-screen text-foreground antialiased selection:bg-primary/20 selection:text-primary">
        @php
            $currentUser = auth()->user();
            $limitService = app(\App\Services\SubscriptionLimitService::class);
            $headerQuota = $currentUser ? $limitService->getQuotaSummary($currentUser) : null;
            $currentTierName = $headerQuota['tier']->name ?? 'Free';
            $initials = collect(explode(' ', trim($currentUser?->name ?? 'U')))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
        @endphp

        <div
            class="min-h-screen flex bg-muted"
            x-data="{
                sidebarOpen: false,
                soonModalOpen: false,
                soonInfo: { title: '', description: '', issue: '' }
            }"
            x-on:livewire:navigated.window="sidebarOpen = false; soonModalOpen = false"
            x-on:open-soon-modal.window="soonInfo = $event.detail; soonModalOpen = true"
            @keydown.escape.window="sidebarOpen = false; soonModalOpen = false"
        >
            <livewire:layout.navigation />

            <!-- Main Content Column -->
            <div class="flex-1 lg:ml-[280px] flex flex-col min-h-screen min-w-0">
                <!-- Sticky Top Header -->
                <header class="flex items-center justify-between h-[76px] lg:h-[90px] border-b border-border bg-white px-5 md:px-8 sticky top-0 z-30 gap-4">
                    <div class="flex items-center gap-3 md:gap-4 min-w-0">
                        <button
                            type="button"
                            @click="sidebarOpen = true"
                            aria-label="Buka menu navigasi"
                            class="lg:hidden size-11 shrink-0 flex items-center justify-center rounded-xl ring-1 ring-border cursor-pointer hover:bg-muted transition-colors text-secondary"
                        >
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" x2="20" y1="12" y2="12" />
                                <line x1="4" x2="20" y1="6" y2="6" />
                                <line x1="4" x2="20" y1="18" y2="18" />
                            </svg>
                        </button>

                        <div class="min-w-0">
                            @if (isset($header))
                                <h1 class="font-bold text-lg sm:text-xl lg:text-2xl text-foreground truncate leading-tight">
                                    {{ $header }}
                                </h1>
                            @endif
                            @if (isset($subtitle))
                                <p class="text-xs sm:text-sm text-secondary truncate mt-0.5">
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                        <!-- User Identity Pill -->
                        <a
                            href="{{ route('profile') }}"
                            wire:navigate
                            class="flex items-center gap-2.5 sm:gap-3 pl-3 pr-1.5 py-1.5 rounded-full bg-muted/60 border border-border hover:bg-muted transition-colors"
                            x-data="{{ json_encode(['name' => $currentUser?->name ?? '']) }}"
                            x-on:profile-updated.window="name = $event.detail.name"
                        >
                            <div class="hidden sm:block text-right leading-tight">
                                <p class="font-semibold text-xs sm:text-sm text-foreground" x-text="name"></p>
                                <p class="text-secondary text-[11px] mt-0.5">Paket {{ $currentTierName }}</p>
                            </div>
                            <div class="size-8 sm:size-9 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center ring-1 ring-border shrink-0">
                                {{ $initials ?: 'U' }}
                            </div>
                        </a>

                        <!-- Explicit Logout Button -->
                        <button
                            type="button"
                            @click="window.Livewire?.dispatch('logout-requested')"
                            class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-full ring-1 ring-border bg-white text-xs sm:text-sm font-semibold text-foreground hover:bg-error/10 hover:text-error hover:ring-error/30 transition-colors cursor-pointer"
                        >
                            <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-5 md:p-8">
                    <div class="max-w-7xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>

            <!-- Soon / Roadmap Feature Info Modal -->
            <div
                x-show="soonModalOpen"
                x-transition.opacity
                @click.self="soonModalOpen = false"
                class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm"
                style="display: none;"
            >
                <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider" x-text="soonInfo.issue || 'Segera Hadir'"></span>
                        <button type="button" @click="soonModalOpen = false" class="size-8 rounded-full hover:bg-muted flex items-center justify-center text-secondary cursor-pointer">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                    <h3 class="font-bold text-xl text-foreground mb-2" x-text="soonInfo.title"></h3>
                    <p class="text-sm text-secondary leading-relaxed mb-6" x-text="soonInfo.description"></p>
                    <div class="flex justify-end">
                        <button type="button" @click="soonModalOpen = false" class="px-6 py-2.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm cursor-pointer">
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
