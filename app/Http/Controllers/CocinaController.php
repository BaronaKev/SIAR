<?php

namespace App\Http\Controllers;

use App\Enums\EstadoPedido;
use App\Models\Pedido;
use Illuminate\View\View;

/**
 * HU021: tablero de cocina con los pedidos por preparar y los listos para entregar.
 */
class CocinaController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('verCocina', Pedido::class);

        $pedidos = Pedido::with(['mesa', 'detalles.producto'])
            ->whereIn('estado', [EstadoPedido::EnPreparacion->value, EstadoPedido::Listo->value])
            ->oldest('fecha')
            ->get()
            ->groupBy(fn (Pedido $pedido) => $pedido->estado->value);

        return view('cocina.index', [
            'enPreparacion' => $pedidos->get(EstadoPedido::EnPreparacion->value, collect()),
            'listos'        => $pedidos->get(EstadoPedido::Listo->value, collect()),
            'refresco'      => (int) config('siar.cocina_refresco_segundos'),
        ]);
    }
}
