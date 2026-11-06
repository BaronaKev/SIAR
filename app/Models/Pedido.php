<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoPedido;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Exceptions\ReglaNegocioException;

/**
 * Diagrama de clases → Pedido.
 */
class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'mesa_id',
        'user_id',
        'fecha',
        'estado',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
        'observaciones',
    ];

    protected $attributes = [
        'estado'    => 'Pendiente',
        'subtotal'  => 0,
        'impuesto'  => 0,
        'descuento' => 0,
        'total'     => 0,
    ];

    protected function casts(): array
    {
        return [
            'fecha'     => 'datetime',
            'estado'    => EstadoPedido::class,
            'subtotal'  => 'decimal:2',
            'impuesto'  => 'decimal:2',
            'descuento' => 'decimal:2',
            'total'     => 'decimal:2',
        ];
    }

    /** Mesa 1 —Ocupa— * Pedido */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    /** Usuario 1 —Realiza— * Pedido */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    /** Pedido 1 ◆—Contiene— * DetallePedido */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    /** Acceso directo a los productos del pedido a través de detalle_pedidos. */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'detalle_pedidos', 'pedido_id', 'producto_id')
            ->withPivot(['id', 'cantidad', 'precio_unitario', 'subtotal'])
            ->withTimestamps()
            ->withTrashed();
    }

    /** Pedido 1 —Genera— 1 Factura */
    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class, 'pedido_id');
    }

    /**
     * Patrón State: cambia el estado solo si la transición es válida.
     *
     * @throws ReglaNegocioException
     */
    public function cambiarEstado(EstadoPedido $destino): void
    {
        if (! $this->estado->puedeTransicionarA($destino)) {
            throw new ReglaNegocioException(
                "No se puede pasar el pedido de '{$this->estado->value}' a '{$destino->value}'."
            );
        }

        $this->update(['estado' => $destino]);
    }

    /** Diagrama: enviarACocina() → HU021 exige al menos un producto. */
    public function enviarACocina(): void
    {
        if (! $this->detalles()->exists()) {
            throw new ReglaNegocioException('El pedido debe tener al menos un producto para enviarlo a cocina.');
        }

        $this->cambiarEstado(EstadoPedido::EnPreparacion);
    }

    /** Diagrama: cancelar() → HU020 solo en Pendiente. */
    public function cancelar(): void
    {
        $this->cambiarEstado(EstadoPedido::Cancelado);
    }

    /** Recalcula subtotal y total a partir de los detalles. */
    public function recalcularTotales(): void
    {
        $subtotal = round((float) $this->detalles()->sum('subtotal'), 2);
        $total    = $subtotal - (float) $this->descuento + (float) $this->impuesto;

        $this->forceFill([
            'subtotal' => $subtotal,
            'total'    => max(0, round($total, 2)),
        ])->save();
    }

    public function scopeEstado($query, EstadoPedido|string|null $estado)
    {
        if ($estado === null || $estado === '') {
            return $query;
        }

        return $query->where('estado', $estado instanceof EstadoPedido ? $estado->value : $estado);
    }
}
