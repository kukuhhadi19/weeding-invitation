<?php

use App\Livewire\Actions\Logout;
use App\Services\SubscriptionLimitService;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function with(SubscriptionLimitService $limitService): array
    {
        $user = auth()->user();

        return [
            'quota' => $user ? $limitService->getQuotaSummary($user) : null,
        ];
    }
}; ?>

<div>
    <!-- Mobile Sidebar Overlay -->
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
        style="display: none;"
    ></div>

    <!-- Fixed Sidebar -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="flex flex-col w-[280px] h-screen fixed left-0 top-0 z-50 bg-white border-r border-border transform transition-transform duration-200 ease-in-out"
    >
        <!-- Brand Header -->
        <div class="flex items-center justify-between border-b border-border h-[76px] lg:h-[90px] px-5 gap-3">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <div class="w-11 h-9 bg-primary rounded-xl flex items-center justify-center shadow-sm shadow-primary/30 text-white">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z" />
                        <path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10" />
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-lg tracking-tight text-foreground block leading-none">UndanganKu</span>
                    <span class="text-[11px] text-secondary font-medium">Undangan Digital</span>
                </div>
            </a>

            <button
                type="button"
                @click="sidebarOpen = false"
                aria-label="Tutup menu navigasi"
                class="lg:hidden size-10 flex bg-white rounded-xl items-center justify-center ring-1 ring-border cursor-pointer text-secondary hover:bg-muted transition-colors"
            >
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex flex-col p-5 gap-6 overflow-y-auto flex-1">
            <div class="flex flex-col gap-3">
                <h3 class="font-semibold text-xs text-secondary uppercase tracking-wider px-2">Menu Utama</h3>
                <div class="flex flex-col gap-1">
                    @php
                        $isDashboard = request()->routeIs('dashboard');
                        $isSubscription = request()->routeIs('subscription.*');
                        $isProfile = request()->routeIs('profile');
                        $isAdmin = request()->routeIs('admin.*');
                    @endphp

                    <a href="{{ route('dashboard') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                        <div class="flex items-center rounded-xl p-3.5 gap-3 transition-all {{ $isDashboard ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            <span class="text-sm {{ $isDashboard ? 'font-semibold' : 'font-medium' }}">Dashboard</span>
                        </div>
                    </a>

                    <a href="{{ route('subscription.index') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                        <div class="flex items-center rounded-xl p-3.5 gap-3 transition-all {{ $isSubscription ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2" />
                                <line x1="2" x2="22" y1="10" y2="10" />
                            </svg>
                            <span class="text-sm {{ $isSubscription ? 'font-semibold' : 'font-medium' }}">Langganan</span>
                        </div>
                    </a>

                    <a href="{{ route('profile') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                        <div class="flex items-center rounded-xl p-3.5 gap-3 transition-all {{ $isProfile ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span class="text-sm {{ $isProfile ? 'font-semibold' : 'font-medium' }}">Akun & Profil</span>
                        </div>
                    </a>
                </div>
            </div>

            @if (auth()->user()?->isAdmin())
                <div class="flex flex-col gap-3">
                    <h3 class="font-semibold text-xs text-secondary uppercase tracking-wider px-2">Admin</h3>
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('admin.dashboard') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                            <div class="flex items-center rounded-xl p-3.5 gap-3 transition-all {{ $isAdmin ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>
                                <span class="text-sm {{ $isAdmin ? 'font-semibold' : 'font-medium' }}">Admin Dashboard</span>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Bottom Quota Summary & Logout -->
            <div class="mt-auto flex flex-col gap-3 pt-4 border-t border-border">
                @if ($quota)
                    <div class="bg-muted rounded-2xl p-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-foreground">Paket {{ $quota['tier']->name }}</span>
                            <span class="text-[11px] font-bold text-primary">
                                {{ $quota['is_unlimited'] ? 'Unlimited' : $quota['used'] . '/' . $quota['limit'] }}
                            </span>
                        </div>
                        @if (! $quota['is_unlimited'])
                            <div class="h-1.5 w-full bg-white rounded-full overflow-hidden mb-2.5">
                                <div class="h-full bg-primary rounded-full" style="width: {{ $quota['usage_percentage'] }}%;"></div>
                            </div>
                        @endif
                        <a href="{{ route('subscription.index') }}" wire:navigate class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors inline-flex items-center gap-1">
                            Kelola Langganan &rarr;
                        </a>
                    </div>
                @endif

                <button
                    wire:click="logout"
                    type="button"
                    class="w-full flex items-center rounded-xl p-3.5 gap-3 text-secondary hover:bg-error/10 hover:text-error transition-all cursor-pointer text-left"
                >
                    <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" x2="9" y1="12" y2="12" />
                    </svg>
                    <span class="text-sm font-medium">Keluar</span>
                </button>
            </div>
        </nav>
    </aside>
</div>
