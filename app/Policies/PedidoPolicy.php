<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EstadoPedido;
use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver pedidos');
    }

    public function view(User $usuario, Pedido $pedido): bool
    {
        return $usuario->can('ver pedidos');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear pedidos');
    }

    /** HU019: cambiar cantidades o quitar productos solo en Pendiente. */
    public function update(User $usuario, Pedido $pedido): bool
    {
        return $usuario->can('editar pedidos') && $pedido->estado->esEditable();
    }

    /** HU018: agregar productos mientras no esté Entregado, Facturado ni Cancelado. */
    public function agregarProductos(User $usuario, Pedido $pedido): bool
    {
        return $usuario->can('editar pedidos') && $pedido->estado->admiteNuevosProductos();
    }

    /**
     * Patrón State + RBAC: la transición debe ser válida y el usuario debe tener
     * el permiso que exige el estado destino (ver EstadoPedido::permisoRequerido()).
     */
    public function cambiarEstado(User $usuario, Pedido $pedido, EstadoPedido $destino): bool
    {
        $permiso = $destino->permisoRequerido();

        return $permiso !== null
            && $pedido->estado->puedeTransicionarA($destino)
            && $usuario->can($permiso);
    }

    /** Vista de cocina (HU021). */
    public function verCocina(User $usuario): bool
    {
        return $usuario->can('preparar pedidos');
    }
}
