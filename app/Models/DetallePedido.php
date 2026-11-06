<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Diagrama de clases → DetallePedido.
 * Al guardar calcula su subtotal y actualiza los totales del pedido.
 */
class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedidos';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    protected function casts(): array
    {
        return [
            'cantidad'        => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal'        => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $detalle): void {
            $detalle->subtotal = $detalle->calcularSubtotal();
        });

        static::saved(fn (self $detalle) => $detalle->pedido->recalcularTotales());
        static::deleted(fn (self $detalle) => $detalle->pedido->recalcularTotales());
    }

    /** Pedido 1 ◆—Contiene— * DetallePedido */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /** Producto 1 —Aparece en— * DetallePedido (incluye productos retirados del menú) */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }

    /** Diagrama: calcularSubtotal(): decimal */
    public function calcularSubtotal(): float
    {
        return round((int) $this->cantidad * (float) $this->precio_unitario, 2);
    }
}
