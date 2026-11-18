<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Space extends Model
{
    protected $fillable = [
        'name',
        'space_type_id',
        'floor',
        'capacity',
        'price_per_hour',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'capacity' => 'integer',
            'price_per_hour' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function spaceType(): BelongsTo
    {
        return $this->belongsTo(SpaceType::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // Solo espacios activos
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // Filtro por tipo de espacio
    public function scopeOfType(Builder $query, int $spaceTypeId): Builder
    {
        return $query->where('space_type_id', $spaceTypeId);
    }

    // Filtro por capacidad mínima
    public function scopeMinCapacity(Builder $query, int $capacity): Builder
    {
        return $query->where('capacity', '>=', $capacity);
    }

    // Espacios sin reservas pending/confirmed que se solapen con el rango
    public function scopeAvailableBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereDoesntHave('reservations', function (Builder $reservation) use ($start, $end) {
            $reservation->blocking()->overlapping($start, $end);
        });
    }
}
