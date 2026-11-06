<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * HU026: métodos de pago admitidos.
 */
enum MetodoPago: string
{
    case Efectivo       = 'Efectivo';
    case TarjetaDebito  = 'Tarjeta débito';
    case TarjetaCredito = 'Tarjeta crédito';
    case Transferencia  = 'Transferencia';

    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }
}
