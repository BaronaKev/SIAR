<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\EstadoReserva;
use App\Models\Reserva;
use App\Models\User;

class ReservaPolicy
{
    public function viewAny(User $usuario): bool
    {
        return $usuario->can('ver reservas');
    }

    public function view(User $usuario, Reserva $reserva): bool
    {
        return $usuario->can('ver reservas');
    }

    public function create(User $usuario): bool
    {
        return $usuario->can('crear reservas');
    }

    public function update(User $usuario, Reserva $reserva): bool
    {
        return $usuario->can('editar reservas') && $reserva->estado === EstadoReserva::Activa;
    }

    public function cancelar(User $usuario, Reserva $reserva): bool
    {
        return $usuario->can('cancelar reservas') && $reserva->estado === EstadoReserva::Activa;
    }
}
