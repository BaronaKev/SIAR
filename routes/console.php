<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Corrige de una vez a todos los usuarios cuyo users.rol_id no coincide con su rol
| en Spatie (model_has_roles) y limpia la caché de permisos.
| Uso: php artisan siar:sincronizar-roles
*/
Artisan::command('siar:sincronizar-roles', function () {
    $filas = [];

    User::withTrashed()->with('rol')->orderBy('id')->each(function (User $usuario) use (&$filas) {
        $corregido = $usuario->sincronizarRol();
        $usuario->refresh();

        $filas[] = [
            $usuario->id,
            $usuario->email,
            $usuario->rol?->name ?? '(sin rol)',
            $usuario->getRoleNames()->implode(', ') ?: '(sin rol)',
            $corregido ? 'corregido' : 'ok',
        ];
    });

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->table(['id', 'correo', 'rol_id →', 'Spatie', 'estado'], $filas);
    $this->info('Roles sincronizados y caché de permisos limpiada.');
})->purpose('Sincroniza users.rol_id con los roles de Spatie y limpia la caché de permisos');
