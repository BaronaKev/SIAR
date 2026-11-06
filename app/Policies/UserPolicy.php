<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('ver usuarios');
    }

    public function view(User $actor, User $usuario): bool
    {
        return $actor->can('ver usuarios');
    }

    public function create(User $actor): bool
    {
        return $actor->can('crear usuarios');
    }

    public function update(User $actor, User $usuario): bool
    {
        return $actor->can('editar usuarios');
    }

    /** Nadie puede eliminar su propia cuenta desde el módulo de usuarios. */
    public function delete(User $actor, User $usuario): bool
    {
        return $actor->can('eliminar usuarios') && $actor->isNot($usuario);
    }
}
