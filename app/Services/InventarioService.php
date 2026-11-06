<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ReglaNegocioException;
use App\Models\Producto;

/**
 * Responsabilidad única: mover el stock de los productos (HU011 / RF011).
 * El modelo Producto marca "Agotado" automáticamente cuando el stock llega a 0.
 */
final class InventarioService
{
    /**
     * @throws ReglaNegocioException
     */
    public function descontar(Producto $producto, int $cantidad): void
    {
        $actual = Producto::query()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();

        if (! $actual->estaDisponible()) {
            throw new ReglaNegocioException("El producto «{$actual->nombre}» está agotado.");
        }

        if ($actual->stock < $cantidad) {
            throw new ReglaNegocioException(
                "Stock insuficiente para «{$actual->nombre}»: quedan {$actual->stock} unidades."
            );
        }

        $actual->stock -= $cantidad;
        $actual->save();

        $producto->setRawAttributes($actual->getAttributes(), true);
    }

    public function reponer(Producto $producto, int $cantidad): void
    {
        $actual = Producto::withTrashed()->whereKey($producto->getKey())->lockForUpdate()->firstOrFail();

        $estabaSinStock = $actual->stock <= 0;
        $actual->stock += $cantidad;

        // Si estaba agotado solo por falta de stock, vuelve a estar disponible.
        if ($estabaSinStock && $actual->stock > 0) {
            $actual->estado = Producto::DISPONIBLE;
        }

        $actual->save();

        $producto->setRawAttributes($actual->getAttributes(), true);
    }
}
