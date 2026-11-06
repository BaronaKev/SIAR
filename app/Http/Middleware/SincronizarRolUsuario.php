<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC: users.rol_id es la fuente de verdad del rol.
 * Si alguien cambió rol_id fuera de la aplicación (por ejemplo, en phpMyAdmin), en la
 * siguiente petición el usuario recibe ese rol en Spatie y con él sus permisos.
 */
class SincronizarRolUsuario
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->user()?->sincronizarRol();

        return $next($request);
    }
}
