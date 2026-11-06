<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Auditoria;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

/**
 * RNF006: registra inicios y cierres de sesión.
 * Laravel descubre este listener automáticamente por los tipos de los métodos handle*.
 */
class RegistrarSesionEnAuditoria
{
    public function handleLogin(Login $evento): void
    {
        Auditoria::create([
            'user_id'     => $evento->user->getAuthIdentifier(),
            'accion'      => 'iniciar sesión',
            'entidad'     => 'User',
            'entidad_id'  => $evento->user->getAuthIdentifier(),
            'ip_address'  => request()->ip(),
            'fecha'       => now(),
        ]);
    }

    public function handleLogout(Logout $evento): void
    {
        if ($evento->user === null) {
            return;
        }

        Auditoria::create([
            'user_id'     => $evento->user->getAuthIdentifier(),
            'accion'      => 'cerrar sesión',
            'entidad'     => 'User',
            'entidad_id'  => $evento->user->getAuthIdentifier(),
            'ip_address'  => request()->ip(),
            'fecha'       => now(),
        ]);
    }
}
