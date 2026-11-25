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
            x-data="{ sidebarOpen: false }"
            x-on:livewire:navigated.window="sidebarOpen = false"
            @keydown.escape.window="sidebarOpen = false"
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

                        @if (isset($backUrl))
                            <a
                                href="{{ $backUrl }}"
                                wire:navigate
                                aria-label="Kembali"
                                class="size-11 shrink-0 flex items-center justify-center rounded-xl ring-1 ring-border hover:bg-muted transition-colors text-secondary hover:text-foreground"
                            >
                                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 19-7-7 7-7" />
                                    <path d="M19 12H5" />
                                </svg>
                            </a>
                        @endif

                        @if (isset($header))
                            <h1 class="font-bold text-lg sm:text-xl lg:text-2xl text-foreground truncate">
                                {{ $header }}
                            </h1>
                        @endif
                    </div>

                    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                        <a
                            href="{{ route('subscription.index') }}"
                            wire:navigate
                            aria-label="Status Langganan dan Kuota"
                            title="Status Langganan & Kuota"
                            class="size-11 flex items-center justify-center rounded-xl ring-1 ring-border relative hover:bg-muted transition-colors text-secondary hover:text-foreground"
                        >
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                            </svg>
                            <span class="absolute top-2.5 right-2.5 size-2 rounded-full ring-2 ring-white {{ ($headerQuota['limit_reached'] ?? false) ? 'bg-error' : 'bg-primary' }}"></span>
                        </a>

                        <a
                            href="{{ route('profile') }}"
                            wire:navigate
                            aria-label="Pengaturan Akun"
                            title="Pengaturan Akun"
                            class="hidden sm:flex size-11 items-center justify-center rounded-xl ring-1 ring-border hover:bg-muted transition-colors text-secondary hover:text-foreground"
                        >
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </a>

                        <!-- User Profile Dropdown -->
                        <div
                            class="relative pl-3 sm:pl-4 ml-1 border-l border-border"
                            x-data="{{ json_encode(['userMenuOpen' => false, 'name' => $currentUser?->name ?? '']) }}"
                            x-on:profile-updated.window="name = $event.detail.name"
                            x-on:livewire:navigated.window="userMenuOpen = false"
                        >
                            <button
                                type="button"
                                @click="userMenuOpen = !userMenuOpen"
                                @click.outside="userMenuOpen = false"
                                class="flex items-center gap-3 hover:opacity-85 transition-opacity cursor-pointer text-left"
                            >
                                <div class="hidden md:block text-right">
                                    <p class="font-semibold text-sm text-foreground leading-tight" x-text="name"></p>
                                    <p class="text-secondary text-xs mt-0.5">Paket {{ $currentTierName }}</p>
                                </div>
                                <div class="size-10 md:size-11 rounded-full bg-primary/10 text-primary font-bold text-sm flex items-center justify-center ring-2 ring-border shrink-0">
                                    {{ $initials ?: 'U' }}
                                </div>
                                <svg class="hidden md:block size-4 text-secondary transition-transform duration-150" :class="{ 'rotate-180': userMenuOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            <div
                                x-show="userMenuOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-3 w-64 bg-white rounded-2xl border border-border shadow-xl py-2 z-50"
                                style="display: none;"
                            >
                                <div class="px-4 py-3 border-b border-border">
                                    <p class="font-semibold text-sm text-foreground truncate" x-text="name"></p>
                                    <p class="text-xs text-secondary truncate">{{ $currentUser?->email }}</p>
                                </div>

                                <div class="p-1.5 flex flex-col gap-0.5">
                                    <a href="{{ route('dashboard') }}" wire:navigate @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-foreground hover:bg-muted transition-colors">
                                        Dashboard
                                    </a>
                                    <a href="{{ route('subscription.index') }}" wire:navigate @click="userMenuOpen = false" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm text-foreground hover:bg-muted transition-colors">
                                        <span>Langganan &amp; Kuota</span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary">{{ $currentTierName }}</span>
                                    </a>
                                    <a href="{{ route('profile') }}" wire:navigate @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-foreground hover:bg-muted transition-colors">
                                        Pengaturan Akun
                                    </a>
                                    @if ($currentUser?->isAdmin())
                                        <a href="{{ route('admin.dashboard') }}" wire:navigate @click="userMenuOpen = false" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-foreground hover:bg-muted transition-colors">
                                            Admin Dashboard
                                        </a>
                                    @endif
                                </div>

                                <div class="p-1.5 border-t border-border">
                                    <button
                                        type="button"
                                        @click="userMenuOpen = false; window.Livewire?.dispatch('logout-requested')"
                                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium text-error hover:bg-error/10 transition-colors cursor-pointer text-left"
                                    >
                                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                            <polyline points="16 17 21 12 16 7" />
                                            <line x1="21" x2="9" y1="12" y2="12" />
                                        </svg>
                                        <span>Keluar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-5 md:p-8">
                    <div class="max-w-6xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
