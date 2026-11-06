<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MetodoPago;
use App\Exceptions\ReglaNegocioException;
use App\Models\Factura;
use App\Models\Pago;
use App\Models\Pedido;
use App\Services\Descuentos\DescuentoFactory;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso de facturación (CU023–CU027).
 */
final class FacturacionService
{
    public function __construct(private readonly DescuentoFactory $descuentos)
    {
    }

    /** CU023 + CU024: genera la factura con el impuesto configurado en config/siar.php. */
    public function generar(Pedido $pedido): Factura
    {
        return Factura::generar($pedido, (float) config('siar.impuesto_porcentaje'));
    }

    /** CU025: descuento por porcentaje o valor fijo (patrón Strategy). */
    public function aplicarDescuento(Factura $factura, string $tipo, float $valor): void
    {
        $factura->aplicarDescuento($this->descuentos->crear($tipo, $valor));
    }

    /**
     * CU026 + CU027: registra un pago. Cuando la factura queda saldada se marca Pagada,
     * el pedido pasa a Facturado y la mesa se libera.
     *
     * @param  array{monto: float|string, metodo: string, monto_recibido?: float|string|null, referencia?: string|null}  $datos
     *
     * @throws ReglaNegocioException
     */
    public function registrarPago(Factura $factura, array $datos): Pago
    {
        return DB::transaction(function () use ($factura, $datos): Pago {
            $pago = new Pago([
                'factura_id'     => $factura->id,
                'monto'          => $datos['monto'],
                'metodo'         => MetodoPago::from($datos['metodo']),
                'monto_recibido' => $datos['monto_recibido'] ?? null,
                'referencia'     => $datos['referencia'] ?? null,
                'fecha'          => now(),
            ]);
            $pago->setRelation('factura', $factura);

            if (! $pago->validarMonto()) {
                throw new ReglaNegocioException(
                    'El monto no es válido: debe ser mayor que 0, no superar el saldo pendiente '
                    . '($' . number_format($factura->saldoPendiente(), 0, ',', '.') . ') '
                    . 'y, en efectivo, el dinero recibido debe cubrirlo.'
                );
            }

            $pago->save();

            if ($factura->saldoPendiente() <= 0) {
                $factura->emitir();
                $factura->pedido->mesa->liberarSiEstaLibre();
            }

            return $pago;
        });
    }
}
