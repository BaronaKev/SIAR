<?php

declare(strict_types=1);

namespace App\Services\Descuentos;

use InvalidArgumentException;

final class DescuentoValorFijo implements EstrategiaDescuento
{
    public function __construct(private readonly float $valor)
    {
        if ($valor < 0) {
            throw new InvalidArgumentException('El valor del descuento no puede ser negativo.');
        }
    }

    public function calcular(float $subtotal): float
    {
        return round($this->valor, 2);
    }

    public function descripcion(): string
    {
        return '$' . number_format($this->valor, 0, ',', '.');
    }
}
