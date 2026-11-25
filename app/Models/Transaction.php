<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'subscription_tier_id',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'amount',
        'status',
        'payment_type',
        'snap_token',
        'midtrans_payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'midtrans_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionTier(): BelongsTo
    {
        return $this->belongsTo(SubscriptionTier::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Return human-readable Indonesian label for the transaction status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'Berhasil',
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_FAILED => 'Gagal',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Return Tailwind badge classes for the transaction status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'bg-success/10 text-success',
            self::STATUS_PENDING => 'bg-warning/20 text-amber-800',
            self::STATUS_EXPIRED => 'bg-muted text-secondary',
            default => 'bg-error/10 text-error',
        };
    }

    /**
     * Return formatted payment channel label for UI display.
     */
    public function paymentTypeLabel(): string
    {
        if (! $this->payment_type) {
            return 'Menunggu Pilihan';
        }

        return match ($this->payment_type) {
            'qris' => 'QRIS',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'bank_transfer' => 'Transfer Bank (VA)',
            'echannel' => 'Mandiri Bill (VA)',
            'permata' => 'Permata VA',
            'bca_klikpay', 'bca_klikbca' => 'BCA Online',
            'credit_card' => 'Kartu Kredit / Debit',
            'cstore' => 'Gerai Retail',
            default => str($this->payment_type)->replace('_', ' ')->title()->toString(),
        };
    }
}
