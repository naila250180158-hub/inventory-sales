<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // Alur pengajuan pembatalan oleh customer
    public const CANCEL_REQUESTED = 'requested';
    public const CANCEL_REJECTED = 'rejected';
    public const CANCEL_APPROVED = 'approved';

    protected $fillable = [
        'user_id', 'sale_number', 'total', 'status', 'sold_at', 'cancelled_at',
        'cancel_status', 'cancel_reason', 'cancel_requested_at', 'cancel_response',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'sold_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'cancel_requested_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Satu penjualan punya banyak item. Foreign key (sale_id) ada di tabel sale_items.
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // Ada permintaan pembatalan dari customer yang belum diputuskan owner
    public function hasPendingCancelRequest(): bool
    {
        return $this->cancel_status === self::CANCEL_REQUESTED;
    }
}
