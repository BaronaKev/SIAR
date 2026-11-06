<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EstadoFactura;
use App\Enums\EstadoPedido;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\User;

class FacturaPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver facturas');
    }

    public function view(User $usuario, Factura $factura): bool
    {
        return $usuario->can('ver facturas');
    }

    /** HU023: solo pedidos Entregados y sin factura previa. */
    public function create(User $usuario, Pedido $pedido): bool
    {
        return $usuario->can('crear facturas')
            && $pedido->estado === EstadoPedido::Entregado
            && $pedido->factura()->doesntExist();
    }

    public function aplicarDescuento(User $usuario, Factura $factura): bool
    {
        return $usuario->can('aplicar descuentos')
            && $factura->estado === EstadoFactura::PendienteDePago
            && $factura->pagos()->doesntExist();
    }

    public function registrarPago(User $usuario, Factura $factura): bool
    {
        return $usuario->can('registrar pagos')
            && $factura->estado === EstadoFactura::PendienteDePago;
    }
}
