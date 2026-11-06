<?php

namespace App\Http\Controllers;

use App\Enums\EstadoFactura;
use App\Http\Requests\Facturacion\PagoRequest;
use App\Models\Factura;
use App\Services\FacturacionService;
use Illuminate\Http\RedirectResponse;

/**
 * CU026: registrar pagos de una factura.
 */
class PagoController extends Controller
{
    public function __construct(private readonly FacturacionService $facturacion)
    {
    }

    public function store(PagoRequest $request, Factura $factura): RedirectResponse
    {
        $this->authorize('registrarPago', $factura);

        $pago = $this->facturacion->registrarPago($factura, $request->validated());

        $mensaje = 'Pago registrado.';

        if ($pago->cambio() > 0) {
            $mensaje .= ' Vueltas: $' . number_format($pago->cambio(), 0, ',', '.') . '.';
        }

        if ($factura->refresh()->estado === EstadoFactura::Pagada) {
            $mensaje .= ' Factura pagada y pedido facturado.';
        }

        return back()->with('success', $mensaje);
    }
}
