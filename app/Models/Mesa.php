<?php

namespace App\Models;

use App\Enums\EstadoMesa;
use App\Enums\EstadoPedido;
use App\Enums\EstadoReserva;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'numero',
        'capacidad',
        'ubicacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
        ];
    }

    /** Mesa 1 —Ocupa— * Pedido */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'mesa_id');
    }

    /** Mesa 1 —Tiene— * Reserva */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'mesa_id');
    }

    public function reservasActivas(): HasMany
    {
        return $this->reservas()->where('estado', EstadoReserva::Activa->value);
    }

    public function pedidosActivos(): HasMany
    {
        return $this->pedidos()->whereIn('estado', EstadoPedido::valoresActivos());
    }

    public function estaEnMantenimiento(): bool
    {
        return $this->estado === EstadoMesa::Mantenimiento->value;
    }

    public function estaDisponible(): bool
    {
        return $this->estado === EstadoMesa::Disponible->value;
    }

    public function cambiarEstado(EstadoMesa $estado): void
    {
        $this->update(['estado' => $estado->value]);
    }

    /**
     * Deja la mesa Disponible si ya no tiene pedidos activos; si aún tiene una
     * reserva activa, vuelve a Reservada.
     */
    public function liberarSiEstaLibre(): void
    {
        if ($this->estaEnMantenimiento() || $this->pedidosActivos()->exists()) {
            return;
        }

        $this->cambiarEstado(
            $this->reservasActivas()->exists() ? EstadoMesa::Reservada : EstadoMesa::Disponible
        );
    }

    /**
     * Scope para filtrar mesas por estado
     */
    public function scopeEstado(Builder $query, ?string $estado): Builder
    {
        return $query->when($estado, fn (Builder $q) => $q->where('estado', $estado));
    }

    /** Búsqueda por número o ubicación (agrupada para no anular otros filtros). */
    public function scopeBuscar(Builder $query, ?string $texto): Builder
    {
        return $query->when($texto, fn (Builder $q) => $q->where(function (Builder $sub) use ($texto) {
            $sub->where('numero', 'like', "%{$texto}%")
                ->orWhere('ubicacion', 'like', "%{$texto}%");
        }));
    }
}
