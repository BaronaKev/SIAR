<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Estados de la mesa. La columna mesas.estado guarda estos valores como texto.
 */
enum EstadoMesa: string
{
    case Disponible    = 'Disponible';
    case Ocupada       = 'Ocupada';
    case Reservada     = 'Reservada';
    case Mantenimiento = 'Mantenimiento';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
