<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    #[On('logout-requested')]
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
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

    <!-- Fixed Sidebar (Matched with reference & dashboard-template.html) -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="flex flex-col w-[280px] h-screen fixed left-0 top-0 z-50 bg-white border-r border-border transform transition-transform duration-200 ease-in-out"
    >
        <!-- Brand Header -->
        <div class="flex items-center justify-between border-b border-border h-[76px] lg:h-[90px] px-5 gap-3">
            <a href="{{ route('dashboard') }}" wire:navigate @click="sidebarOpen = false" class="flex items-center gap-3">
                <div class="w-11 h-9 bg-primary rounded-xl flex items-center justify-center shadow-sm shadow-primary/30 text-white shrink-0">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.2 8.4c.5.38.8.97.8 1.6v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V10a2 2 0 0 1 .8-1.6l8-6a2 2 0 0 1 2.4 0l8 6Z" />
                        <path d="m22 10-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 10" />
                    </svg>
                </div>
                <div>
                    <span class="font-bold text-lg tracking-tight text-foreground block leading-none">UndanganKu</span>
                    <span class="text-[11px] text-secondary font-medium">{{ auth()->user()?->isAdmin() ? 'Admin Studio' : 'Dashboard' }}</span>
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
        @php
            $isAdminUser = auth()->user()?->isAdmin() ?? false;
            $isDashboard = request()->routeIs('dashboard');
            $isAccount = request()->routeIs('profile') || request()->routeIs('subscription.*');
            $isTransactions = request()->routeIs('transactions.*');
            $isAdminOverview = request()->routeIs('admin.dashboard');
            $isAdminCategories = request()->routeIs('admin.categories.*');
        @endphp

        <nav class="flex flex-col p-5 gap-6 overflow-y-auto flex-1">
            @if ($isAdminUser)
                <!-- Admin Studio MENU Section -->
                <div class="flex flex-col gap-3">
                    <h3 class="font-semibold text-[11px] text-secondary uppercase tracking-wider px-2">Menu</h3>
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('admin.dashboard') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                            <div class="flex items-center rounded-xl p-3 gap-3 transition-all {{ $isAdminOverview ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="9" x="3" y="3" rx="1" />
                                    <rect width="7" height="5" x="14" y="3" rx="1" />
                                    <rect width="7" height="9" x="14" y="12" rx="1" />
                                    <rect width="7" height="5" x="3" y="16" rx="1" />
                                </svg>
                                <span class="text-sm {{ $isAdminOverview ? 'font-semibold' : 'font-medium' }}">Overview</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                            <div class="flex items-center rounded-xl p-3 gap-3 transition-all {{ $isAdminCategories ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z" />
                                    <circle cx="7.5" cy="7.5" r=".5" fill="currentColor" />
                                </svg>
                                <span class="text-sm {{ $isAdminCategories ? 'font-semibold' : 'font-medium' }}">Kategori</span>
                            </div>
                        </a>

                        <button
                            type="button"
                            @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'CRUD Katalog Template', description: 'Pengelolaan template Blade, preview thumbnail, dan tema terkunci (themeConfig) akan dibangun pada Issue #6.', issue: 'Issue #6' })"
                            class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="7" x="3" y="3" rx="1" />
                                    <rect width="7" height="7" x="14" y="3" rx="1" />
                                    <rect width="7" height="7" x="14" y="14" rx="1" />
                                    <rect width="7" height="7" x="3" y="14" rx="1" />
                                </svg>
                                <span class="text-sm font-medium truncate">Template</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                        </button>

                        <button
                            type="button"
                            @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'Upload & Media Storage', description: 'Pipeline unggah aset template dan optimasi WebP galeri akan diaktifkan penuh pada Issue #6 & Issue #11.', issue: 'Issue #6 & #11' })"
                            class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" x2="12" y1="3" y2="15" />
                                </svg>
                                <span class="text-sm font-medium truncate">Upload Media</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                        </button>
                    </div>
                </div>
            @else
                <!-- Top User MENU Section -->
                <div class="flex flex-col gap-3">
                    <h3 class="font-semibold text-[11px] text-secondary uppercase tracking-wider px-2">Menu</h3>
                    <div class="flex flex-col gap-1">
                        <a href="{{ route('dashboard') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                            <div class="flex items-center rounded-xl p-3 gap-3 transition-all {{ $isDashboard ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="9" x="3" y="3" rx="1" />
                                    <rect width="7" height="5" x="14" y="3" rx="1" />
                                    <rect width="7" height="9" x="14" y="12" rx="1" />
                                    <rect width="7" height="5" x="3" y="16" rx="1" />
                                </svg>
                                <span class="text-sm {{ $isDashboard ? 'font-semibold' : 'font-medium' }}">Dashboard</span>
                            </div>
                        </a>

                        <button
                            type="button"
                            @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'Katalog Template', description: 'Katalog template dinamis dari database dan pratinjau penuh akan tersedia pada tahap Issue #6 & Issue #7.', issue: 'Issue #6 & #7' })"
                            class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="7" height="7" x="3" y="3" rx="1" />
                                    <rect width="7" height="7" x="14" y="3" rx="1" />
                                    <rect width="7" height="7" x="14" y="14" rx="1" />
                                    <rect width="7" height="7" x="3" y="14" rx="1" />
                                </svg>
                                <span class="text-sm font-medium truncate">Template</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                        </button>

                        <button
                            type="button"
                            @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'Undangan Saya', description: 'Daftar undangan digital, editor split-screen, dan manajemen status publikasi akan hadir pada Issue #8.', issue: 'Issue #8' })"
                            class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>
                                <span class="text-sm font-medium truncate">Undangan Saya</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                        </button>

                        <button
                            type="button"
                            @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'Statistik & Analitik', description: 'Laporan jumlah pengunjung undangan, konfirmasi RSVP, dan grafik kehadiran akan tersedia pada Issue #14.', issue: 'Issue #14' })"
                            class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" x2="18" y1="20" y2="10" />
                                    <line x1="12" x2="12" y1="20" y2="4" />
                                    <line x1="6" x2="6" y1="20" y2="14" />
                                </svg>
                                <span class="text-sm font-medium truncate">Statistik</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                        </button>

                        <button
                            type="button"
                            @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'Daftar Tamu & RSVP', description: 'Manajemen daftar tamu, link undangan personal, serta ekspor CSV/Excel akan hadir pada Issue #10 & Issue #13.', issue: 'Issue #10 & #13' })"
                            class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                        >
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span class="text-sm font-medium truncate">Daftar Tamu</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Bottom AKUN Section -->
            <div class="mt-auto flex flex-col gap-3">
                <h3 class="font-semibold text-[11px] text-secondary uppercase tracking-wider px-2">Akun</h3>
                <div class="flex flex-col gap-1">
                    <a href="{{ route('profile') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                        <div class="flex items-center rounded-xl p-3 gap-3 transition-all {{ $isAccount ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span class="text-sm {{ $isAccount ? 'font-semibold' : 'font-medium' }}">Akun</span>
                        </div>
                    </a>

                    @if ($isAdminUser)
                        <a href="{{ route('dashboard') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                            <div class="flex items-center rounded-xl p-3 gap-3 transition-all {{ $isDashboard ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                    <polyline points="9 22 9 12 15 12 15 22" />
                                </svg>
                                <span class="text-sm {{ $isDashboard ? 'font-semibold' : 'font-medium' }}">Dashboard User</span>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('transactions.index') }}" wire:navigate @click="sidebarOpen = false" class="group cursor-pointer">
                            <div class="flex items-center rounded-xl p-3 gap-3 transition-all {{ $isTransactions ? 'bg-primary/10 text-primary' : 'text-secondary hover:bg-muted hover:text-foreground' }}">
                                <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                                    <path d="M14 8H8" />
                                    <path d="M16 12H8" />
                                    <path d="M13 16H8" />
                                </svg>
                                <span class="text-sm {{ $isTransactions ? 'font-semibold' : 'font-medium' }}">Riwayat Transaksi</span>
                            </div>
                        </a>
                    @endif

                    <button
                        type="button"
                        @click="sidebarOpen = false; $dispatch('open-soon-modal', { title: 'Pengaturan Lanjutan', description: 'Preferensi notifikasi email otomatis dan pengaturan lanjutan platform akan ditambahkan pada tahap penyempurnaan.', issue: 'Mendatang' })"
                        class="w-full flex items-center justify-between rounded-xl p-3 gap-3 text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer text-left"
                    >
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span class="text-sm font-medium truncate">Pengaturan</span>
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-muted text-secondary shrink-0">Segera</span>
                    </button>
                </div>
            </div>
        </nav>
    </aside>
</div>
