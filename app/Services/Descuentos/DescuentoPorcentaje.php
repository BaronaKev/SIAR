<?php

declare(strict_types=1);

namespace App\Services\Descuentos;

use InvalidArgumentException;

final class DescuentoPorcentaje implements EstrategiaDescuento
{
    public function __construct(private readonly float $porcentaje)
    {
        if ($porcentaje < 0 || $porcentaje > 100) {
            throw new InvalidArgumentException('El porcentaje de descuento debe estar entre 0 y 100.');
        }
    }

    public function calcular(float $subtotal): float
    {
        return round($subtotal * $this->porcentaje / 100, 2);
    }

    public function descripcion(): string
    {
        return rtrim(rtrim(number_format($this->porcentaje, 2, ',', '.'), '0'), ',') . ' %';
    }
}
