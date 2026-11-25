<x-app-layout>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Kelola kategori template, desain undangan, pengguna, dan transaksi Midtrans platform.
    </x-slot>

    @php
        $totalUsers = \App\Models\User::count();
        $verifiedUsers = \App\Models\User::whereNotNull('email_verified_at')->count();
        $paidUsers = \App\Models\User::whereHas('subscriptionTier', fn ($query) => $query->where('price_monthly', '>', 0))->count();

        $successfulRevenue = (int) \App\Models\Transaction::where('status', \App\Models\Transaction::STATUS_SUCCESS)->sum('amount');
        $successTransactions = \App\Models\Transaction::where('status', \App\Models\Transaction::STATUS_SUCCESS)->count();
        $pendingTransactions = \App\Models\Transaction::where('status', \App\Models\Transaction::STATUS_PENDING)->count();

        $recentTransactions = \App\Models\Transaction::with(['user', 'subscriptionTier'])
            ->latest()
            ->take(20)
            ->get();

        $tierDistributions = \App\Models\SubscriptionTier::withCount('users')
            ->orderBy('price_monthly')
            ->get();
    @endphp

    <div
        class="flex flex-col gap-8"
        x-data="{
            activeStatus: 'all',
            searchQuery: '',
            matchesRow(status, orderId, userName, userEmail) {
                const statusOk = this.activeStatus === 'all' || this.activeStatus === status;
                const q = this.searchQuery.trim().toLowerCase();
                const searchOk = q === ''
                    || orderId.toLowerCase().includes(q)
                    || userName.toLowerCase().includes(q)
                    || userEmail.toLowerCase().includes(q);
                return statusOk && searchOk;
            }
        }"
    >
        <!-- Dark Hero Banner (Baseline: templates/dashboard-template.html) -->
        <section class="bg-foreground rounded-3xl p-8 md:p-10 flex items-center justify-between relative overflow-hidden shadow-xl text-white">
            <div class="relative z-10 max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/10 text-white text-xs font-semibold rounded-full mb-4 backdrop-blur-sm border border-white/20 uppercase tracking-wider">
                    <span class="size-1.5 rounded-full bg-success"></span>
                    Admin Platform &bull; {{ Auth::user()->role }}
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4 leading-tight">
                    Kelola Platform Undangan Digital Secara Terpusat
                </h2>
                <p class="text-white/75 mb-8 text-base md:text-lg leading-relaxed">
                    Halo, {{ Auth::user()->name }}! Pantau pertumbuhan pengguna, pendapatan langganan Midtrans Snap, serta kesiapan katalog template dalam satu panel.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="#admin-transactions"
                        class="px-8 py-3.5 rounded-full bg-primary hover:bg-primary-hover text-white font-bold text-sm transition-colors shadow-lg shadow-primary/30 inline-flex items-center gap-2"
                    >
                        Pantau Transaksi
                    </a>
                    <button
                        type="button"
                        @click="$dispatch('open-soon-modal', { title: 'CRUD Kategori Template', description: 'Pengelolaan kategori template pernikahan (nama, slug, ikon, urutan tampil) akan dibangun pada GitHub Issue #5.', issue: 'Issue #5' })"
                        class="px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/20 transition-colors cursor-pointer"
                    >
                        Kelola Kategori
                    </button>
                    <button
                        type="button"
                        @click="$dispatch('open-soon-modal', { title: 'CRUD Katalog Template', description: 'Unggah Blade template, thumbnail, dan konfigurasi themeConfig JSON terkunci akan tersedia pada GitHub Issue #6.', issue: 'Issue #6' })"
                        class="px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/20 transition-colors cursor-pointer"
                    >
                        Kelola Template
                    </button>
                </div>
            </div>

            <!-- Decorative Elements matching templates/dashboard-template.html -->
            <div class="hidden md:block absolute right-0 top-0 bottom-0 w-1/3 bg-gradient-to-l from-primary/20 to-transparent pointer-events-none"></div>
            <svg class="hidden md:block absolute -right-10 -top-10 size-64 text-white/5 rotate-12 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z" />
            </svg>
            <svg class="hidden md:block absolute right-20 bottom-10 size-24 text-primary/20 -rotate-12 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
            </svg>
        </section>

        <!-- Platform Key Metrics (4-Column Grid) -->
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Total Pengguna</p>
                <p class="mt-2 text-3xl font-bold text-foreground">{{ number_format($totalUsers, 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs text-secondary">{{ $verifiedUsers }} akun terverifikasi email</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Pelanggan Berbayar</p>
                <p class="mt-2 text-3xl font-bold text-primary">{{ number_format($paidUsers, 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs text-secondary">Basic, Premium &amp; Enterprise aktif</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Pendapatan Sukses</p>
                <p class="mt-2 text-3xl font-bold text-foreground">Rp {{ number_format($successfulRevenue, 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs text-success font-medium">{{ $successTransactions }} transaksi terkonfirmasi</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-secondary">Transaksi Pending</p>
                <p class="mt-2 text-3xl font-bold text-foreground">{{ number_format($pendingTransactions, 0, ',', '.') }}</p>
                <p class="mt-1.5 text-xs text-secondary">Menunggu konfirmasi Midtrans</p>
            </div>
        </section>

        <!-- Tier Breakdown Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($tierDistributions as $tierItem)
                <div class="bg-white rounded-2xl border border-border p-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium text-secondary">Tier {{ $tierItem->name }}</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $tierItem->users_count }} User</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $tierItem->price_monthly === 0 ? 'bg-muted text-secondary' : 'bg-primary/10 text-primary' }}">
                        {{ $tierItem->price_monthly === 0 ? 'Gratis' : 'Rp ' . number_format($tierItem->price_monthly, 0, ',', '.') }}
                    </span>
                </div>
            @endforeach
        </section>

        <!-- Filter Pills + Transaction Monitoring Table -->
        <section id="admin-transactions" class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="p-6 border-b border-border flex flex-col gap-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-xl text-foreground">Monitoring Transaksi Platform</h3>
                        <p class="text-xs sm:text-sm text-secondary mt-0.5">Daftar 20 transaksi checkout Midtrans terbaru dari seluruh pengguna.</p>
                    </div>
                    <input
                        type="search"
                        x-model="searchQuery"
                        placeholder="Cari Order ID, nama, atau email user..."
                        class="w-full md:w-72 px-4 py-2.5 bg-muted/50 border border-border rounded-full text-sm text-foreground placeholder:text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>

                <!-- Horizontal Filter Pills matching templates/dashboard-template.html -->
                <div class="overflow-x-auto scrollbar-hide">
                    <div class="flex gap-3 min-w-max pb-1">
                        @foreach ([
                            'all' => 'Semua Transaksi',
                            'success' => 'Berhasil',
                            'pending' => 'Menunggu',
                            'failed' => 'Gagal',
                            'expired' => 'Kedaluwarsa',
                        ] as $key => $label)
                            <button
                                type="button"
                                @click="activeStatus = '{{ $key }}'"
                                :class="activeStatus === '{{ $key }}'
                                    ? 'bg-foreground text-white shadow-sm'
                                    : 'bg-white border border-border text-secondary hover:border-primary hover:text-primary'"
                                class="px-5 py-2 rounded-full font-medium text-xs sm:text-sm transition-all cursor-pointer"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($recentTransactions->isEmpty())
                <div class="p-10 text-center text-sm text-secondary">
                    Belum ada transaksi checkout Midtrans pada platform.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-card-grey/40 text-[11px] font-bold uppercase tracking-wider text-secondary">
                                <th class="px-6 py-3.5">Pengguna</th>
                                <th class="px-6 py-3.5">Order ID</th>
                                <th class="px-6 py-3.5">Paket</th>
                                <th class="px-6 py-3.5">Metode</th>
                                <th class="px-6 py-3.5">Nominal</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border text-sm">
                            @foreach ($recentTransactions as $trx)
                                <tr
                                    x-show="matchesRow({{ json_encode($trx->status) }}, {{ json_encode($trx->midtrans_order_id) }}, {{ json_encode($trx->user?->name ?? '') }}, {{ json_encode($trx->user?->email ?? '') }})"
                                    class="hover:bg-card-grey/30 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-foreground">{{ $trx->user?->name ?? '-' }}</p>
                                        <p class="text-xs text-secondary">{{ $trx->user?->email ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-foreground whitespace-nowrap">
                                        {{ $trx->midtrans_order_id }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-foreground whitespace-nowrap">
                                        Paket {{ $trx->subscriptionTier?->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-secondary whitespace-nowrap">
                                        {{ $trx->paymentTypeLabel() }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-foreground whitespace-nowrap">
                                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $trx->statusBadgeClass() }}">
                                            {{ $trx->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-secondary whitespace-nowrap">
                                        {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
