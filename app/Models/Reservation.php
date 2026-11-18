<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'space_id',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $appends = [
        'duration_hours',
        'status_label',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    // Reservas que cruzan con el rango [$start, $end)
    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query->where('start_time', '<', $end)
            ->where('end_time', '>', $start);
    }

    // Reservas que ocupan el espacio: pending o confirmed
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Reservas que aún no empiezan
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_time', '>', now());
    }

    // Duración en horas (ej. 1.5)
    protected function durationHours(): Attribute
    {
        return Attribute::make(
            get: fn () => round($this->start_time->diffInMinutes($this->end_time) / 60, 2),
        );
    }

    // Estado legible en español
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                self::STATUS_PENDING => 'Pendiente',
                self::STATUS_CONFIRMED => 'Confirmada',
                self::STATUS_CANCELLED => 'Cancelada',
                self::STATUS_COMPLETED => 'Completada',
                default => $this->status,
            },
        );
    }
}
