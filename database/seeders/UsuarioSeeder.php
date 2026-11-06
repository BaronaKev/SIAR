<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Usuarios de prueba, uno por rol. Solo para desarrollo local.
 * Contraseña de todos: "password" (cámbiala si el sistema se publica).
 */
class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['name' => 'Administrador SIAR', 'email' => 'admin@siar.test',    'rol' => 'administrador'],
            ['name' => 'Mesero SIAR',        'email' => 'mesero@siar.test',   'rol' => 'mesero'],
            ['name' => 'Cocinero SIAR',      'email' => 'cocinero@siar.test', 'rol' => 'cocinero'],
            ['name' => 'Cajero SIAR',        'email' => 'cajero@siar.test',   'rol' => 'cajero'],
        ];

        foreach ($usuarios as $datos) {
            $usuario = User::withTrashed()->updateOrCreate(
                ['email' => $datos['email']],
                [
                    'name'              => $datos['name'],
                    'password'          => 'password',
                    'estado'            => true,
                ]
            );

            if ($usuario->trashed()) {
                $usuario->restore();
            }

            $usuario->asignarRol($datos['rol']);
        }
    }
}
