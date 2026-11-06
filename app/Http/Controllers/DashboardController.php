<?php

namespace App\Http\Controllers;

use App\Enums\EstadoFactura;
use App\Enums\EstadoMesa;
use App\Enums\EstadoPedido;
use App\Enums\EstadoReserva;
use App\Models\Factura;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use Illuminate\View\View;

/**
 * Indicadores del día para el tablero principal.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'indicadores' => [
                'mesasDisponibles'  => Mesa::where('estado', EstadoMesa::Disponible->value)->count(),
                'mesasTotal'        => Mesa::count(),
                'productosAgotados' => Producto::where('estado', Producto::AGOTADO)->count(),
                'productosTotal'    => Producto::count(),
                'pedidosActivos'    => Pedido::whereIn('estado', EstadoPedido::valoresActivos())->count(),
                'enCocina'          => Pedido::where('estado', EstadoPedido::EnPreparacion->value)->count(),
                'ventasHoy'         => (float) Factura::where('estado', EstadoFactura::Pagada->value)
                    ->whereDate('fecha', today())
                    ->sum('total'),
                'reservasHoy'       => Reserva::where('estado', EstadoReserva::Activa->value)
                    ->whereDate('fecha', today())
                    ->count(),
            ],
        ]);
    }
}
