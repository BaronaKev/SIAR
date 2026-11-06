<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class AuditoriaPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver auditoria');
    }
}
