<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;

class ProductoPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver productos');
    }

    public function view(User $usuario, Producto $producto): bool
    {
        return $usuario->can('ver productos');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear productos');
    }

    public function update(User $usuario, Producto $producto): bool
    {
        return $usuario->can('editar productos');
    }

    public function delete(User $usuario, Producto $producto): bool
    {
        return $usuario->can('eliminar productos');
    }

    /** RF011: el cocinero puede marcar un producto como disponible o agotado. */
    public function cambiarDisponibilidad(User $usuario, Producto $producto): bool
    {
        return $usuario->can('cambiar disponibilidad productos');
    }
}
