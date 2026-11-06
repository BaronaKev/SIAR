<?php

namespace App\Http\Controllers;

use App\Enums\EstadoFactura;
use App\Enums\EstadoPedido;
use App\Enums\MetodoPago;
use App\Http\Requests\Facturacion\DescuentoRequest;
use App\Models\Factura;
use App\Models\Pedido;
use App\Services\Descuentos\DescuentoFactory;
use App\Services\FacturacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Módulo 5 – Facturación (CU023–CU025, CU027). Autorización: FacturaPolicy.
 */
class FacturaController extends Controller
{
    public function __construct(private readonly FacturacionService $facturacion)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Factura::class);

        $estado = $request->string('estado')->toString() ?: null;

        $facturas = Factura::with('pedido.mesa')
            ->when($estado, fn ($query) => $query->where('estado', $estado))
            ->latest('fecha')
            ->paginate(15)
            ->withQueryString();

        // Pedidos entregados que esperan factura.
        $porFacturar = Pedido::with('mesa')
            ->where('estado', EstadoPedido::Entregado->value)
            ->whereDoesntHave('factura')
            ->oldest('fecha')
            ->get();

        return view('facturas.index', [
            'facturas'    => $facturas,
            'porFacturar' => $porFacturar,
            'estados'     => EstadoFactura::cases(),
            'estado'      => $estado,
        ]);
    }

    /** CU023: genera la factura de un pedido entregado. */
    public function store(Pedido $pedido): RedirectResponse
    {
        $this->authorize('create', [Factura::class, $pedido]);

        $factura = $this->facturacion->generar($pedido);

        return redirect()->route('facturas.show', $factura)
            ->with('success', "Factura {$factura->numero} generada.");
    }

    public function show(Factura $factura): View
    {
        $this->authorize('view', $factura);

        $factura->load(['pedido.mesa', 'pedido.usuario', 'pedido.detalles.producto', 'pagos']);

        return view('facturas.show', [
            'factura'        => $factura,
            'metodos'        => MetodoPago::cases(),
            'tiposDescuento' => DescuentoFactory::tipos(),
        ]);
    }

    /** CU025 */
    public function aplicarDescuento(DescuentoRequest $request, Factura $factura): RedirectResponse
    {
        $this->authorize('aplicarDescuento', $factura);

        $this->facturacion->aplicarDescuento(
            $factura,
            $request->validated('tipo'),
            (float) $request->validated('valor'),
        );

        return back()->with('success', 'Descuento aplicado. Total recalculado.');
    }

    /** CU027: comprobante para imprimir o guardar como PDF desde el navegador. */
    public function comprobante(Factura $factura): View
    {
        $this->authorize('view', $factura);

        abort_unless($factura->estado === EstadoFactura::Pagada, 404, 'La factura aún no está pagada.');

        $factura->load(['pedido.mesa', 'pedido.detalles.producto', 'pagos']);

        return view('facturas.comprobante', compact('factura'));
    }
}
