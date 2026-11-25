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
            $currentTierName = $currentUser?->subscriptionTier?->name ?? 'Free';
            $initials = collect(explode(' ', trim($currentUser?->name ?? 'U')))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
        @endphp

        <div class="min-h-screen flex bg-muted" x-data="{ sidebarOpen: false }">
            <livewire:layout.navigation />

            <!-- Main Content Column -->
            <div class="flex-1 lg:ml-[280px] flex flex-col min-h-screen min-w-0">
                <!-- Sticky Top Header -->
                <header class="flex items-center justify-between h-[76px] lg:h-[90px] border-b border-border bg-white px-5 md:px-8 sticky top-0 z-30">
                    <div class="flex items-center gap-4 min-w-0">
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

                        @if (isset($header))
                            <div class="font-bold text-xl md:text-2xl text-foreground truncate">
                                {{ $header }}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <a
                            href="{{ route('subscription.index') }}"
                            wire:navigate
                            class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-primary/10 text-primary hover:bg-primary/15 transition-colors"
                        >
                            <span class="size-1.5 rounded-full bg-primary"></span>
                            Paket {{ $currentTierName }}
                        </a>

                        <a
                            href="{{ route('profile') }}"
                            wire:navigate
                            class="flex items-center gap-3 pl-3 ml-1 border-l border-border hover:opacity-80 transition-opacity"
                        >
                            <div
                                class="hidden md:block text-right"
                                x-data="{{ json_encode(['name' => $currentUser?->name ?? '']) }}"
                                x-on:profile-updated.window="name = $event.detail.name"
                            >
                                <p class="font-semibold text-sm text-foreground" x-text="name"></p>
                                <p class="text-secondary text-xs">{{ $currentUser?->email }}</p>
                            </div>
                            <div class="size-10 md:size-11 rounded-full bg-primary/10 text-primary font-bold text-sm flex items-center justify-center ring-2 ring-border">
                                {{ $initials ?: 'U' }}
                            </div>
                        </a>
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
