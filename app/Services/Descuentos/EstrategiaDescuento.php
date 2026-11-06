<?php

declare(strict_types=1);

namespace App\Services\Descuentos;

/**
 * Patrón Strategy (HU025): cada tipo de descuento sabe calcular su valor.
 * Para agregar un tipo nuevo se crea otra clase, sin modificar Factura (principio Abierto/Cerrado).
 */
interface EstrategiaDescuento
{
    /** Valor del descuento, en pesos, para el subtotal dado. */
    public function calcular(float $subtotal): float;

    /** Texto para auditoría y comprobante. Ej.: "10 %" o "$5.000". */
    public function descripcion(): string;
}
