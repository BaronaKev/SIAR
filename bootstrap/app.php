<?php

use App\Exceptions\ReglaNegocioException;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SincronizarRolUsuario;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // RBAC: alias de Spatie para usar ->middleware('permission:...') o ('role:...') en rutas.
        $middleware->alias([
            'role'               => RoleMiddleware::class,
            'permission'         => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Expulsa a los usuarios desactivados aunque tengan la sesión abierta.
        $middleware->appendToGroup('web', EnsureUserIsActive::class);

        // Mantiene users.rol_id y los roles de Spatie iguales (rol_id manda).
        $middleware->appendToGroup('web', SincronizarRolUsuario::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Una regla de negocio incumplida vuelve al formulario con el mensaje, sin error 500.
        $exceptions->render(function (ReglaNegocioException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
