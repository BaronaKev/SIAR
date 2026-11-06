<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * RBAC de SIAR: catálogo de permisos y su asignación a cada rol.
 * Se puede ejecutar varias veces: crea lo que falta y sincroniza los permisos.
 */
class RoleSeeder extends Seeder
{
    /** @var array<string, list<string>> Permisos agrupados por módulo */
    public const PERMISOS = [
        'usuarios'  => ['ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios'],
        'mesas'     => ['ver mesas', 'crear mesas', 'editar mesas', 'eliminar mesas', 'cambiar estado mesas'],
        'menu'      => ['ver productos', 'crear productos', 'editar productos', 'eliminar productos', 'cambiar disponibilidad productos'],
        'pedidos'   => [
            'ver pedidos', 'crear pedidos', 'editar pedidos', 'cancelar pedidos',
            'enviar pedidos a cocina', 'preparar pedidos', 'entregar pedidos',
        ],
        'facturas'  => ['ver facturas', 'crear facturas', 'aplicar descuentos', 'registrar pagos'],
        'reservas'  => ['ver reservas', 'crear reservas', 'editar reservas', 'cancelar reservas'],
        'auditoria' => ['ver auditoria'],
    ];

    /** @var array<string, list<string>> Permisos por rol (el administrador recibe todos) */
    public const ROLES = [
        'mesero' => [
            'ver mesas', 'cambiar estado mesas', 'ver productos',
            'ver pedidos', 'crear pedidos', 'editar pedidos', 'cancelar pedidos',
            'enviar pedidos a cocina', 'entregar pedidos',
            'ver reservas', 'crear reservas', 'editar reservas', 'cancelar reservas',
        ],
        'cocinero' => [
            'ver productos', 'cambiar disponibilidad productos',
            'ver pedidos', 'preparar pedidos',
        ],
        'cajero' => [
            'ver mesas', 'cambiar estado mesas',
            'ver pedidos',
            'ver facturas', 'crear facturas', 'aplicar descuentos', 'registrar pagos',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_merge(...array_values(self::PERMISOS)) as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Permiso de la versión anterior, reemplazado por "cancelar pedidos".
        Permission::where('name', 'eliminar pedidos')->delete();

        Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web'])
            ->syncPermissions(Permission::all());

        foreach (self::ROLES as $rol => $permisos) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web'])->syncPermissions($permisos);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
