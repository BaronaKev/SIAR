<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoReserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Exceptions\ReglaNegocioException;

/**
 * Diagrama de clases → Reserva.
 */
class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'mesa_id',
        'user_id',
        'nombre_cliente',
        'fecha',
        'hora',
        'personas',
        'estado',
    ];

    protected $attributes = [
        'estado' => 'Activa',
    ];

    protected function casts(): array
    {
        return [
            'fecha'    => 'date',
            'personas' => 'integer',
            'estado'   => EstadoReserva::class,
        ];
    }

    /** Mesa 1 —Tiene— * Reserva */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    /** Usuario 1 —Gestiona— * Reserva */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /** Diagrama: cancelar() */
    public function cancelar(): void
    {
        if ($this->estado !== EstadoReserva::Activa) {
            throw new ReglaNegocioException('Solo se pueden cancelar reservas activas.');
        }

        $this->update(['estado' => EstadoReserva::Cancelada]);
    }

    /** El cliente llegó y ocupó la mesa. */
    public function cumplir(): void
    {
        if ($this->estado !== EstadoReserva::Activa) {
            throw new ReglaNegocioException('Solo se pueden cumplir reservas activas.');
        }

        $this->update(['estado' => EstadoReserva::Cumplida]);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', EstadoReserva::Activa->value);
    }
}
