<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;

/**
 * Las categorías son parte del menú: usan los mismos permisos que los productos.
 */
class CategoriaPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver productos');
    }

    public function view(User $usuario, Categoria $categoria): bool
    {
        return $usuario->can('ver productos');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear productos');
    }

    public function update(User $usuario, Categoria $categoria): bool
    {
        return $usuario->can('editar productos');
    }

    public function delete(User $usuario, Categoria $categoria): bool
    {
        return $usuario->can('eliminar productos');
    }
}
