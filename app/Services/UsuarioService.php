<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso de gestión de usuarios (CU001–CU004).
 */
final class UsuarioService
{
    private const ROL_ADMIN = 'administrador';

    /**
     * @param  array{name: string, email: string, password: string, role: string, estado?: bool}  $datos
     */
    public function crear(array $datos): User
    {
        return DB::transaction(function () use ($datos): User {
            $usuario = User::create([
                'name'     => $datos['name'],
                'email'    => $datos['email'],
                'password' => $datos['password'], // el cast "hashed" del modelo la cifra (RNF004)
                'estado'   => $datos['estado'] ?? true,
            ]);

            $usuario->asignarRol($datos['role']);

            return $usuario;
        });
    }

    /**
     * @param  array{name: string, email: string, password?: string|null, role: string, estado?: bool}  $datos
     *
     * @throws ReglaNegocioException
     */
    public function actualizar(User $usuario, array $datos, User $actor): void
    {
        $estado = $datos['estado'] ?? true;

        if ($actor->is($usuario)) {
            if (! $estado) {
                throw new ReglaNegocioException('No puedes desactivar tu propia cuenta.');
            }

            if ($usuario->hasRole(self::ROL_ADMIN) && $datos['role'] !== self::ROL_ADMIN) {
                throw new ReglaNegocioException('No puedes quitarte a ti mismo el rol de administrador.');
            }
        }

        DB::transaction(function () use ($usuario, $datos, $estado): void {
            $usuario->fill([
                'name'   => $datos['name'],
                'email'  => $datos['email'],
                'estado' => $estado,
            ]);

            if (! empty($datos['password'])) {
                $usuario->password = $datos['password'];
            }

            $usuario->save();
            $usuario->asignarRol($datos['role']);
        });
    }

    public function eliminar(User $usuario): void
    {
        $usuario->delete(); // SoftDeletes: conserva su historial de pedidos y auditoría
    }
}
