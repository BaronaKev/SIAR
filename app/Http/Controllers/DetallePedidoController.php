<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pedidos\ActualizarDetalleRequest;
use App\Http\Requests\Pedidos\AgregarProductoRequest;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;

/**
 * Productos de un pedido (CU018 agregar, CU019 modificar o quitar).
 */
class DetallePedidoController extends Controller
{
    public function __construct(private readonly PedidoService $pedidos)
    {
    }

    public function store(AgregarProductoRequest $request, Pedido $pedido): RedirectResponse
    {
        $this->authorize('agregarProductos', $pedido);

        $producto = Producto::findOrFail($request->validated('producto_id'));
        $this->pedidos->agregarProducto($pedido, $producto, (int) $request->validated('cantidad'));

        return back()->with('success', "Se agregó {$producto->nombre} al pedido.");
    }

    public function update(ActualizarDetalleRequest $request, Pedido $pedido, DetallePedido $detalle): RedirectResponse
    {
        $this->authorize('update', $pedido);

        $this->pedidos->actualizarCantidad($detalle, (int) $request->validated('cantidad'));

        return back()->with('success', 'Cantidad actualizada.');
    }

    public function destroy(Pedido $pedido, DetallePedido $detalle): RedirectResponse
    {
        $this->authorize('update', $pedido);

        $this->pedidos->quitarProducto($detalle);

        return back()->with('success', 'Producto quitado del pedido.');
    }
}
