<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoFactura: string
{
    case PendienteDePago = 'Pendiente de pago';
    case Pagada          = 'Pagada';
    case Anulada         = 'Anulada';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
