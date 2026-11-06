<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Mesa;
use App\Models\User;

class MesaPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver mesas');
    }

    public function view(User $usuario, Mesa $mesa): bool
    {
        return $usuario->can('ver mesas');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear mesas');
    }

    public function update(User $usuario, Mesa $mesa): bool
    {
        return $usuario->can('editar mesas');
    }

    public function delete(User $usuario, Mesa $mesa): bool
    {
        return $usuario->can('eliminar mesas');
    }

    public function cambiarEstado(User $usuario, Mesa $mesa): bool
    {
        return $usuario->can('cambiar estado mesas');
    }
}
