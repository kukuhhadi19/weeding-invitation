<?php

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'all';

    public string $search = '';

    #[Url]
    public ?string $order = null;

    #[Url(as: 'snap_result')]
    public ?string $snapResult = null;

    public ?Transaction $selectedTransaction = null;

    public function setStatus(string $status): void
    {
        $allowed = ['all', Transaction::STATUS_PENDING, Transaction::STATUS_SUCCESS, Transaction::STATUS_FAILED, Transaction::STATUS_EXPIRED];
        $this->status = in_array($status, $allowed, true) ? $status : 'all';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function continuePayment(int $transactionId): void
    {
        $transaction = auth()->user()
            ->transactions()
            ->whereKey($transactionId)
            ->first();

        if (! $transaction || ! $transaction->isPending() || ! $transaction->snap_token) {
            session()->flash('error', 'Transaksi tidak lagi tersedia untuk dilanjutkan.');

            return;
        }

        $this->dispatch('open-midtrans-snap', snapToken: $transaction->snap_token, orderId: $transaction->midtrans_order_id);
    }

    public function inspectTransaction(int $transactionId): void
    {
        $this->selectedTransaction = auth()->user()
            ->transactions()
            ->with('subscriptionTier')
            ->whereKey($transactionId)
            ->first();
    }

    public function closeDetail(): void
    {
        $this->selectedTransaction = null;
    }

    public function with(): array
    {
        $user = auth()->user();

        $query = $user->transactions()
            ->with('subscriptionTier')
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $term = '%'.trim($this->search).'%';
            $query->where('midtrans_order_id', 'like', $term);
        }

        return [
            'transactions' => $query->paginate(10),
            'totalSpent' => (int) $user->transactions()->where('status', Transaction::STATUS_SUCCESS)->sum('amount'),
            'pendingCount' => $user->transactions()->where('status', Transaction::STATUS_PENDING)->count(),
        ];
    }
}; ?>

<div x-data="{ snapWarning: null }" x-on:snap-unavailable.window="snapWarning = $event.detail.message">
    <x-slot name="header">
        Riwayat Transaksi
    </x-slot>

    <x-slot name="subtitle">
        Pantau status pembayaran Midtrans dan riwayat langganan akun Anda.
    </x-slot>

    <div class="flex flex-col gap-6">
        <!-- Callback Status Banner -->
        @if ($snapResult)
            @php
                $bannerConfig = match ($snapResult) {
                    'success' => ['bg' => 'bg-success/10 border-success/25', 'text' => 'Pembayaran Anda tercatat selesai' . ($order ? ' (' . $order . ')' : '') . '. Paket akan aktif otomatis segera setelah webhook Midtrans diterima.'],
                    'pending' => ['bg' => 'bg-warning/15 border-warning/40', 'text' => 'Pembayaran menunggu penyelesaian' . ($order ? ' untuk Order ID ' . $order : '') . '. Anda dapat melanjutkan pembayaran dari daftar di bawah.'],
                    'error' => ['bg' => 'bg-error/10 border-error/25', 'text' => 'Terjadi kendala saat memproses pembayaran. Silakan coba kembali.'],
                    default => ['bg' => 'bg-muted border-border', 'text' => 'Jendela pembayaran ditutup. Jika transaksi masih tertunda, klik Lanjutkan Bayar di bawah.'],
                };
            @endphp
            <div class="p-4 rounded-2xl border {{ $bannerConfig['bg'] }} text-sm text-foreground">
                {{ $bannerConfig['text'] }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-error/10 border border-error/20 text-sm text-foreground">
                {{ session('error') }}
            </div>
        @endif

        <template x-if="snapWarning">
            <div class="p-4 rounded-2xl bg-error/10 border border-error/20 text-sm text-foreground flex items-center justify-between gap-3">
                <span x-text="snapWarning"></span>
                <button type="button" @click="snapWarning = null" class="text-xs font-bold text-secondary hover:text-foreground cursor-pointer">Tutup</button>
            </div>
        </template>

        <!-- Summary Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl border border-border p-5">
                <p class="text-xs font-medium text-secondary">Total Pembayaran Sukses</p>
                <p class="mt-2 text-2xl font-bold text-foreground">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-5">
                <p class="text-xs font-medium text-secondary">Menunggu Pembayaran</p>
                <p class="mt-2 text-2xl font-bold text-foreground">{{ $pendingCount }} Transaksi</p>
            </div>

            <div class="bg-white rounded-2xl border border-border p-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-medium text-secondary">Butuh Upgrade?</p>
                    <p class="mt-1 text-sm font-bold text-foreground">Lihat semua tier paket</p>
                </div>
                <a href="{{ route('subscription.index') }}" wire:navigate class="px-4 py-2 rounded-full bg-primary text-white text-xs font-bold hover:bg-primary-hover transition-colors shrink-0">
                    Pilih Paket
                </a>
            </div>
        </div>

        <!-- Filters & Table Card -->
        <section class="bg-white rounded-2xl border border-border overflow-hidden">
            <div class="p-5 md:p-6 border-b border-border flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="overflow-x-auto scrollbar-hide">
                    <div class="flex gap-2 min-w-max">
                        @foreach ([
                            'all' => 'Semua',
                            'pending' => 'Menunggu',
                            'success' => 'Berhasil',
                            'failed' => 'Gagal',
                            'expired' => 'Kedaluwarsa',
                        ] as $key => $label)
                            <button
                                type="button"
                                wire:click="setStatus('{{ $key }}')"
                                class="px-4 py-2 rounded-full text-xs sm:text-sm font-medium transition-all cursor-pointer {{ $status === $key ? 'bg-foreground text-white' : 'bg-muted/70 text-secondary hover:text-foreground' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="relative w-full lg:w-72">
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari Order ID..."
                        class="w-full px-4 py-2 bg-muted/50 border border-border rounded-full text-sm text-foreground placeholder:text-secondary outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"
                    />
                </div>
            </div>

            @if ($transactions->isEmpty())
                <div class="p-10 text-center flex flex-col items-center">
                    <div class="size-16 rounded-full bg-muted flex items-center justify-center text-secondary mb-4">
                        <svg class="size-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                            <path d="M14 8H8" />
                            <path d="M16 12H8" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-foreground">Belum Ada Transaksi</h3>
                    <p class="text-sm text-secondary mt-1 max-w-md">
                        Anda belum memiliki riwayat transaksi langganan sesuai filter saat ini.
                    </p>
                    <a href="{{ route('subscription.index') }}" wire:navigate class="mt-5 px-6 py-2.5 rounded-full bg-primary text-white text-sm font-bold hover:bg-primary-hover transition-colors">
                        Lihat Pilihan Paket
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border bg-card-grey/40 text-[11px] font-bold uppercase tracking-wider text-secondary">
                                <th class="px-6 py-3.5">Tanggal</th>
                                <th class="px-6 py-3.5">Order ID</th>
                                <th class="px-6 py-3.5">Paket</th>
                                <th class="px-6 py-3.5">Metode</th>
                                <th class="px-6 py-3.5">Nominal</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border text-sm">
                            @foreach ($transactions as $trx)
                                <tr class="hover:bg-card-grey/30 transition-colors">
                                    <td class="px-6 py-4 text-secondary whitespace-nowrap">
                                        {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs font-semibold text-foreground whitespace-nowrap">
                                        {{ $trx->midtrans_order_id }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-foreground whitespace-nowrap">
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
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2">
                                            @if ($trx->isPending() && $trx->snap_token)
                                                <button
                                                    type="button"
                                                    wire:click="continuePayment({{ $trx->id }})"
                                                    class="px-3.5 py-1.5 rounded-full bg-primary text-white text-xs font-bold hover:bg-primary-hover transition-colors cursor-pointer"
                                                >
                                                    Lanjutkan Bayar
                                                </button>
                                            @endif
                                            <button
                                                type="button"
                                                wire:click="inspectTransaction({{ $trx->id }})"
                                                class="px-3.5 py-1.5 rounded-full ring-1 ring-border text-xs font-semibold text-foreground hover:bg-muted transition-colors cursor-pointer"
                                            >
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($transactions->hasPages())
                    <div class="p-4 border-t border-border">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>

    <!-- Transaction Detail Modal -->
    @if ($selectedTransaction)
        <div class="fixed inset-0 bg-black/60 z-[100] flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-border flex items-center justify-between">
                    <h3 class="font-bold text-lg text-foreground">Detail Transaksi</h3>
                    <button type="button" wire:click="closeDetail" class="size-8 rounded-full hover:bg-muted flex items-center justify-center text-secondary cursor-pointer">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">Order ID</span>
                        <span class="font-mono font-semibold text-foreground">{{ $selectedTransaction->midtrans_order_id }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">ID Midtrans</span>
                        <span class="font-mono text-foreground">{{ $selectedTransaction->midtrans_transaction_id ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">Paket Langganan</span>
                        <span class="font-semibold text-foreground">Paket {{ $selectedTransaction->subscriptionTier?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">Metode Pembayaran</span>
                        <span class="text-foreground">{{ $selectedTransaction->paymentTypeLabel() }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">Total Pembayaran</span>
                        <span class="font-bold text-foreground">Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">Status</span>
                        <span class="font-bold text-foreground">{{ $selectedTransaction->statusLabel() }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="text-secondary">Dibayar Pada</span>
                        <span class="text-foreground">{{ $selectedTransaction->paid_at ? $selectedTransaction->paid_at->translatedFormat('d M Y, H:i') : '-' }}</span>
                    </div>
                </div>
                <div class="p-6 border-t border-border bg-card-grey/30 flex justify-end">
                    <button type="button" wire:click="closeDetail" class="px-6 py-2.5 rounded-full bg-primary text-white text-sm font-bold hover:bg-primary-hover cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <x-midtrans-snap-script />
    @endpush
</div>
