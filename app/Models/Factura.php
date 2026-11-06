<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoFactura;
use App\Enums\EstadoPedido;
use App\Services\Descuentos\EstrategiaDescuento;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReglaNegocioException;

/**
 * Diagrama de clases → Factura.
 */
class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'pedido_id',
        'fecha',
        'subtotal',
        'porcentaje_impuesto',
        'impuesto',
        'descuento',
        'total',
        'estado',
    ];

    protected $attributes = [
        'estado'              => 'Pendiente de pago',
        'subtotal'            => 0,
        'porcentaje_impuesto' => 0,
        'impuesto'            => 0,
        'descuento'           => 0,
        'total'               => 0,
    ];

    protected function casts(): array
    {
        return [
            'fecha'               => 'datetime',
            'estado'              => EstadoFactura::class,
            'subtotal'            => 'decimal:2',
            'porcentaje_impuesto' => 'decimal:2',
            'impuesto'            => 'decimal:2',
            'descuento'           => 'decimal:2',
            'total'               => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Consecutivo legible (FAC-000001) a partir del id: no tiene condiciones de carrera.
        static::created(function (self $factura): void {
            $factura->forceFill(['numero' => sprintf('FAC-%06d', $factura->id)])->saveQuietly();
        });
    }

    /** Pedido 1 —Genera— 1 Factura */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /** Factura 1 —Registra— * Pago */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'factura_id');
    }

    /**
     * Diagrama: generar() → HU023: solo pedidos Entregados y sin factura previa.
     *
     * @throws ReglaNegocioException
     */
    public static function generar(Pedido $pedido, float $porcentajeImpuesto): self
    {
        if ($pedido->estado !== EstadoPedido::Entregado) {
            throw new ReglaNegocioException('Solo se pueden facturar pedidos en estado Entregado.');
        }

        if ($pedido->factura()->exists()) {
            throw new ReglaNegocioException('El pedido ya tiene una factura.');
        }

        $factura = new self([
            'pedido_id'           => $pedido->id,
            'subtotal'            => $pedido->subtotal,
            'porcentaje_impuesto' => $porcentajeImpuesto,
        ]);
        $factura->recalcular();

        return $factura;
    }

    /** Diagrama: calcularImpuesto(): decimal → se aplica sobre (subtotal - descuento). */
    public function calcularImpuesto(): float
    {
        $base = (float) $this->subtotal - (float) $this->descuento;

        return round($base * (float) $this->porcentaje_impuesto / 100, 2);
    }

    /**
     * Diagrama: aplicarDescuento() → HU025. Patrón Strategy: el cálculo lo hace la estrategia
     * recibida (porcentaje o valor fijo); la factura solo valida que no supere el subtotal.
     *
     * @throws ReglaNegocioException
     */
    public function aplicarDescuento(EstrategiaDescuento $estrategia): void
    {
        $descuento = $estrategia->calcular((float) $this->subtotal);

        if ($descuento > (float) $this->subtotal) {
            throw new ReglaNegocioException('El descuento no puede superar el subtotal.');
        }

        $this->descuento = $descuento;
        $this->recalcular();
    }

    /** Recalcula impuesto y total y guarda la factura. */
    public function recalcular(): void
    {
        $this->impuesto = $this->calcularImpuesto();
        $this->total    = round((float) $this->subtotal - (float) $this->descuento + (float) $this->impuesto, 2);
        $this->save();
    }

    public function totalPagado(): float
    {
        return round((float) $this->pagos()->sum('monto'), 2);
    }

    public function saldoPendiente(): float
    {
        return max(0, round((float) $this->total - $this->totalPagado(), 2));
    }

    /**
     * Diagrama: emitir() → HU026/HU027: con el pago completo, la factura queda Pagada
     * y el pedido pasa a Facturado.
     *
     * @throws ReglaNegocioException
     */
    public function emitir(): void
    {
        if ($this->saldoPendiente() > 0) {
            throw new ReglaNegocioException('La factura tiene saldo pendiente; registre el pago completo.');
        }

        DB::transaction(function (): void {
            $this->update(['estado' => EstadoFactura::Pagada]);
            $this->pedido->cambiarEstado(EstadoPedido::Facturado);
        });
    }
}
