<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EstadoMesa;
use App\Enums\EstadoPedido;
use App\Exceptions\ReglaNegocioException;
use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso del módulo de pedidos (CU017–CU022).
 * Depende de InventarioService para el stock (inversión de dependencias por inyección).
 */
final class PedidoService
{
    public function __construct(private readonly InventarioService $inventario)
    {
    }

    /**
     * CU017: crea el pedido con sus productos y ocupa la mesa.
     *
     * @param  array<int|string, int|string>  $productos  [producto_id => cantidad]
     *
     * @throws ReglaNegocioException
     */
    public function crear(Mesa $mesa, User $usuario, array $productos, ?string $observaciones = null): Pedido
    {
        if ($mesa->estaEnMantenimiento()) {
            throw new ReglaNegocioException("La mesa {$mesa->numero} está en mantenimiento.");
        }

        $productos = array_filter(
            array_map('intval', $productos),
            fn (int $cantidad) => $cantidad > 0
        );

        if ($productos === []) {
            throw new ReglaNegocioException('El pedido debe tener al menos un producto.');
        }

        return DB::transaction(function () use ($mesa, $usuario, $productos, $observaciones): Pedido {
            $pedido = Pedido::create([
                'mesa_id'       => $mesa->id,
                'user_id'       => $usuario->id,
                'fecha'         => now(),
                'observaciones' => $observaciones,
            ]);

            foreach ($productos as $productoId => $cantidad) {
                $this->agregarProducto($pedido, Producto::findOrFail($productoId), $cantidad);
            }

            $mesa->cambiarEstado(EstadoMesa::Ocupada);

            return $pedido->refresh();
        });
    }

    /**
     * CU018: agrega un producto. Si ya está en el pedido, suma la cantidad.
     *
     * @throws ReglaNegocioException
     */
    public function agregarProducto(Pedido $pedido, Producto $producto, int $cantidad): DetallePedido
    {
        if (! $pedido->estado->admiteNuevosProductos()) {
            throw new ReglaNegocioException("No se pueden agregar productos a un pedido {$pedido->estado->value}.");
        }

        if ($cantidad < 1) {
            throw new ReglaNegocioException('La cantidad debe ser al menos 1.');
        }

        return DB::transaction(function () use ($pedido, $producto, $cantidad): DetallePedido {
            $this->inventario->descontar($producto, $cantidad);

            $detalle = $pedido->detalles()->where('producto_id', $producto->id)->first();

            if ($detalle !== null) {
                $detalle->cantidad += $cantidad;
                $detalle->save();

                return $detalle;
            }

            return $pedido->detalles()->create([
                'producto_id'     => $producto->id,
                'cantidad'        => $cantidad,
                'precio_unitario' => $producto->precio,
            ]);
        });
    }

    /**
     * CU019: cambia la cantidad de un producto (solo en Pendiente).
     *
     * @throws ReglaNegocioException
     */
    public function actualizarCantidad(DetallePedido $detalle, int $nuevaCantidad): void
    {
        $this->asegurarEditable($detalle->pedido);

        if ($nuevaCantidad < 1) {
            throw new ReglaNegocioException('La cantidad debe ser al menos 1. Para quitar el producto usa «Quitar».');
        }

        DB::transaction(function () use ($detalle, $nuevaCantidad): void {
            $diferencia = $nuevaCantidad - $detalle->cantidad;

            if ($diferencia > 0) {
                $this->inventario->descontar($detalle->producto, $diferencia);
            } elseif ($diferencia < 0) {
                $this->inventario->reponer($detalle->producto, -$diferencia);
            }

            $detalle->cantidad = $nuevaCantidad;
            $detalle->save();
        });
    }

    /**
     * CU019: quita un producto del pedido y devuelve su stock.
     *
     * @throws ReglaNegocioException
     */
    public function quitarProducto(DetallePedido $detalle): void
    {
        $this->asegurarEditable($detalle->pedido);

        DB::transaction(function () use ($detalle): void {
            $this->inventario->reponer($detalle->producto, $detalle->cantidad);
            $detalle->delete();
        });
    }

    /**
     * CU020 / CU021 y flujo de cocina: aplica la transición de estado (patrón State).
     * Facturado lo asigna FacturacionService al registrar el pago.
     *
     * @throws ReglaNegocioException
     */
    public function cambiarEstado(Pedido $pedido, EstadoPedido $destino): void
    {
        DB::transaction(function () use ($pedido, $destino): void {
            match ($destino) {
                EstadoPedido::EnPreparacion => $pedido->enviarACocina(),
                EstadoPedido::Cancelado     => $this->cancelar($pedido),
                EstadoPedido::Facturado     => throw new ReglaNegocioException('Un pedido se factura desde el módulo de facturación.'),
                default                     => $pedido->cambiarEstado($destino),
            };
        });
    }

    private function cancelar(Pedido $pedido): void
    {
        $pedido->cancelar();

        foreach ($pedido->detalles()->with('producto')->get() as $detalle) {
            $this->inventario->reponer($detalle->producto, $detalle->cantidad);
        }

        $pedido->mesa->liberarSiEstaLibre();
    }

    private function asegurarEditable(Pedido $pedido): void
    {
        if (! $pedido->estado->esEditable()) {
            throw new ReglaNegocioException('Solo se pueden modificar pedidos en estado Pendiente.');
        }
    }
}
