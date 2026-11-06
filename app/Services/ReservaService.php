<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoMesa;
use App\Exceptions\ReglaNegocioException;
use App\Models\Mesa;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso de reservas (CU015 / CU016).
 */
final class ReservaService
{
    /**
     * CU015: solo se reservan mesas Disponibles; la mesa pasa a Reservada.
     *
     * @param  array{mesa_id: int|string, nombre_cliente: string, fecha: string, hora: string, personas: int|string}  $datos
     *
     * @throws ReglaNegocioException
     */
    public function crear(array $datos, User $usuario): Reserva
    {
        $mesa = Mesa::findOrFail($datos['mesa_id']);

        if (! $mesa->estaDisponible()) {
            throw new ReglaNegocioException("La mesa {$mesa->numero} no está disponible para reservar ({$mesa->estado}).");
        }

        if ((int) $datos['personas'] > $mesa->capacidad) {
            throw new ReglaNegocioException("La mesa {$mesa->numero} tiene capacidad para {$mesa->capacidad} personas.");
        }

        return DB::transaction(function () use ($datos, $usuario, $mesa): Reserva {
            $reserva = Reserva::create([...$datos, 'user_id' => $usuario->id]);
            $mesa->cambiarEstado(EstadoMesa::Reservada);

            return $reserva;
        });
    }

    /**
     * @param  array{nombre_cliente: string, fecha: string, hora: string, personas: int|string}  $datos
     *
     * @throws ReglaNegocioException
     */
    public function actualizar(Reserva $reserva, array $datos): void
    {
        if ((int) $datos['personas'] > $reserva->mesa->capacidad) {
            throw new ReglaNegocioException("La mesa {$reserva->mesa->numero} tiene capacidad para {$reserva->mesa->capacidad} personas.");
        }

        $reserva->update($datos);
    }

    /** CU016: cancela la reserva y libera la mesa si no tiene otro uso. */
    public function cancelar(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva): void {
            $reserva->cancelar();
            $reserva->mesa->liberarSiEstaLibre();
        });
    }

    /** El cliente llegó: la reserva se cumple y la mesa queda lista para tomar el pedido. */
    public function cumplir(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva): void {
            $reserva->cumplir();
            $reserva->mesa->liberarSiEstaLibre();
        });
    }
}
