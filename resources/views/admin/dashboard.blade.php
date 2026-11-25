<x-app-layout>
    <x-slot name="header">
        Admin Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Kelola kategori template, desain undangan, serta pantau statistik platform.
    </x-slot>

    <div class="flex flex-col gap-6">
        <section class="bg-white rounded-2xl border border-border p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-foreground">Selamat Datang di Admin Platform, {{ Auth::user()->name }}!</h3>
                <p class="mt-1 text-sm text-secondary flex items-center gap-2">
                    <span>Peran akun:</span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary uppercase tracking-wider">
                        {{ Auth::user()->role }}
                    </span>
                </p>
            </div>
            <a href="{{ route('subscription.index') }}" wire:navigate class="px-5 py-2.5 rounded-full bg-muted text-foreground font-semibold text-sm hover:bg-card-grey transition-colors">
                Lihat Paket Langganan
            </a>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl border border-border p-6">
                <div class="text-sm font-medium text-secondary">Kelola Kategori &amp; Template</div>
                <div class="mt-2 text-2xl font-bold text-foreground">Issue #5 &amp; #6</div>
                <div class="mt-1 text-xs text-secondary">Menunggu implementasi CRUD admin</div>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <div class="text-sm font-medium text-secondary">Pembayaran Midtrans</div>
                <div class="mt-2 text-2xl font-bold text-foreground">Issue #4</div>
                <div class="mt-1 text-xs text-secondary">Integrasi Midtrans Snap &amp; Webhook</div>
            </div>

            <div class="bg-white rounded-2xl border border-border p-6">
                <div class="text-sm font-medium text-secondary">Platform Analytics &amp; Moderasi</div>
                <div class="mt-2 text-2xl font-bold text-foreground">Issue #14</div>
                <div class="mt-1 text-xs text-secondary">Statistik user, transaksi, &amp; ucapan tamu</div>
            </div>
        </div>
    </div>
</x-app-layout>
