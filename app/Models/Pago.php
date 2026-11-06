<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MetodoPago;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Diagrama de clases → Pago.
 */
class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'factura_id',
        'monto',
        'monto_recibido',
        'metodo',
        'referencia',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'monto'          => 'decimal:2',
            'monto_recibido' => 'decimal:2',
            'metodo'         => MetodoPago::class,
            'fecha'          => 'datetime',
        ];
    }

    /** Factura 1 —Registra— * Pago */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id');
    }

    /**
     * Diagrama: validarMonto(): boolean → HU026.
     *  - El monto es mayor que cero y no supera el saldo pendiente de la factura.
     *  - En efectivo, el dinero recibido cubre el monto.
     */
    public function validarMonto(): bool
    {
        $monto = (float) $this->monto;

        if ($monto <= 0 || $this->factura === null) {
            return false;
        }

        $saldo = $this->factura->saldoPendiente() + ($this->exists ? (float) $this->getOriginal('monto') : 0);

        if ($monto > $saldo) {
            return false;
        }

        if ($this->metodo === MetodoPago::Efectivo && $this->monto_recibido !== null) {
            return (float) $this->monto_recibido >= $monto;
        }

        return true;
    }

    /** Vueltas para pagos en efectivo. */
    public function cambio(): float
    {
        if ($this->monto_recibido === null) {
            return 0.0;
        }

        return max(0, round((float) $this->monto_recibido - (float) $this->monto, 2));
    }
}
