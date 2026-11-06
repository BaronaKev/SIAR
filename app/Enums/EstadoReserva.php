<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoReserva: string
{
    case Activa    = 'Activa';
    case Cumplida  = 'Cumplida';
    case Cancelada = 'Cancelada';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
