<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketType extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'sale_start_at' => 'datetime',
            'sale_end_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** "sold" sudah termasuk tiket yang sedang ditahan pesanan belum-bayar. */
    public function remaining(): int
    {
        return max(0, $this->quota - $this->sold);
    }

    public function isOnSale(): bool
    {
        if ($this->sale_start_at && now()->lt($this->sale_start_at)) {
            return false;
        }

        if ($this->sale_end_at && now()->gt($this->sale_end_at)) {
            return false;
        }

        return true;
    }
}
