<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public const DRAFT = 'draft';
    public const PUBLISHED = 'published';

    // Hanya diisi dari data yang sudah divalidasi di server, jangan pernah dari request()->all().
    protected $guarded = [];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    /** URL event memakai slug, bukan id. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class)->orderBy('sort_order')->orderBy('price');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::PUBLISHED);
    }
}
