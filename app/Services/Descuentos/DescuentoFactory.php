<?php

declare(strict_types=1);

namespace App\Services\Descuentos;

use InvalidArgumentException;

/**
 * Crea la estrategia de descuento a partir de lo que llega del formulario.
 */
final class DescuentoFactory
{
    public const PORCENTAJE = 'porcentaje';
    public const VALOR_FIJO = 'valor_fijo';

    /** @return list<string> */
    public static function tipos(): array
    {
        return [self::PORCENTAJE, self::VALOR_FIJO];
    }

    public function crear(string $tipo, float $valor): EstrategiaDescuento
    {
        return match ($tipo) {
            self::PORCENTAJE => new DescuentoPorcentaje($valor),
            self::VALOR_FIJO => new DescuentoValorFijo($valor),
            default          => throw new InvalidArgumentException("Tipo de descuento no soportado: {$tipo}"),
        };
    }
}
