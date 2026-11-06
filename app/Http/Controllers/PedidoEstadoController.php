<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedidos\CambiarEstadoPedidoRequest;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;

/**
 * Cambia el estado de un pedido (patrón State + RBAC):
 * mesero → cocina / entregado / cancelado, cocinero → listo.
 */
class PedidoEstadoController extends Controller
{
    public function __construct(private readonly PedidoService $pedidos)
    {
    }

    public function __invoke(CambiarEstadoPedidoRequest $request, Pedido $pedido): RedirectResponse
    {
        $destino = $request->destino();

        $this->authorize('cambiarEstado', [$pedido, $destino]);

        $this->pedidos->cambiarEstado($pedido, $destino);

        return back()->with('success', "Pedido #{$pedido->id} ahora está {$destino->value}.");
    }
}
