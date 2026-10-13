<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    public const PENDING = 'pending';             // menunggu pembayaran, stok sedang ditahan
    public const PAID = 'paid';                   // lunas, tiket terbit
    public const EXPIRED = 'expired';             // waktu bayar habis, stok dilepas
    public const CANCELLED = 'cancelled';         // ditolak/dibatalkan gateway, stok dilepas
    public const REFUND_NEEDED = 'refund_needed'; // bayar masuk terlambat & stok sudah habis -> refund manual
    public const REFUNDED = 'refunded';           // dikembalikan, tiket dibatalkan

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /** URL pesanan memakai kode (INV-...), bukan id berurutan. */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function newCode(): string
    {
        return 'INV-'.now()->format('ymd').'-'.strtoupper(Str::random(8));
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
