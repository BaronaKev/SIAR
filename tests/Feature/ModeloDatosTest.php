<?php

namespace Tests\Feature;

use App\Enums\EstadoFactura;
use App\Enums\EstadoPedido;
use App\Enums\EstadoReserva;
use App\Enums\MetodoPago;
use App\Models\Auditoria;
use App\Models\DetallePedido;
use App\Models\Factura;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\User;
use App\Exceptions\ReglaNegocioException;
use App\Services\Descuentos\DescuentoPorcentaje;
use App\Services\Descuentos\DescuentoValorFijo;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/**
 * Verifica las tablas y relaciones del diagrama de clases de SIAR.
 */
class ModeloDatosTest extends TestCase
{
    use RefreshDatabase;

    private User $mesero;
    private Mesa $mesa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->mesero = User::where('email', 'mesero@siar.test')->firstOrFail();
        $this->mesa   = Mesa::where('numero', '1')->firstOrFail();
    }

    private function crearPedidoConProductos(): Pedido
    {
        $pedido = Pedido::create(['mesa_id' => $this->mesa->id, 'user_id' => $this->mesero->id]);

        foreach (Producto::disponibles()->take(2)->get() as $producto) {
            DetallePedido::create([
                'pedido_id'       => $pedido->id,
                'producto_id'     => $producto->id,
                'cantidad'        => 2,
                'precio_unitario' => $producto->precio,
            ]);
        }

        return $pedido->refresh();
    }

    public function test_los_seeders_crean_roles_usuarios_mesas_y_menu(): void
    {
        $this->assertTrue($this->mesero->hasRole('mesero'));
        $this->assertSame(10, Mesa::count());
        $this->assertGreaterThan(0, Producto::count());
    }

    public function test_el_detalle_calcula_subtotal_y_actualiza_el_pedido(): void
    {
        $pedido = $this->crearPedidoConProductos();

        $esperado = round($pedido->detalles->sum(fn ($d) => $d->cantidad * (float) $d->precio_unitario), 2);

        $this->assertSame(EstadoPedido::Pendiente, $pedido->estado);
        $this->assertEquals($esperado, (float) $pedido->subtotal);
        $this->assertEquals($esperado, (float) $pedido->total);
        $this->assertCount(2, $pedido->productos);
        $this->assertTrue($pedido->mesa->is($this->mesa));
        $this->assertTrue($pedido->usuario->is($this->mesero));
    }

    public function test_patron_state_bloquea_transiciones_invalidas(): void
    {
        $pedido = $this->crearPedidoConProductos();

        $this->expectException(LogicException::class);
        $pedido->cambiarEstado(EstadoPedido::Facturado);
    }

    public function test_no_se_envia_a_cocina_un_pedido_vacio(): void
    {
        $pedido = Pedido::create(['mesa_id' => $this->mesa->id, 'user_id' => $this->mesero->id]);

        $this->expectException(LogicException::class);
        $pedido->enviarACocina();
    }

    public function test_flujo_completo_pedido_factura_pago(): void
    {
        $pedido = $this->crearPedidoConProductos();
        $pedido->enviarACocina();
        $pedido->cambiarEstado(EstadoPedido::Listo);
        $pedido->cambiarEstado(EstadoPedido::Entregado);

        $factura = Factura::generar($pedido, 8);
        $factura->aplicarDescuento(new DescuentoPorcentaje(10));

        $base = (float) $pedido->subtotal * 0.9;
        $this->assertEquals(round($base * 0.08, 2), (float) $factura->impuesto);
        $this->assertEquals(round($base * 1.08, 2), (float) $factura->total);
        $this->assertSame('FAC-' . str_pad((string) $factura->id, 6, '0', STR_PAD_LEFT), $factura->fresh()->numero);

        $pago = new Pago([
            'factura_id'     => $factura->id,
            'monto'          => $factura->total,
            'monto_recibido' => (float) $factura->total + 5000,
            'metodo'         => MetodoPago::Efectivo,
        ]);
        $this->assertTrue($pago->validarMonto());
        $pago->save();
        $this->assertEquals(5000, $pago->cambio());

        $factura->emitir();

        $this->assertSame(EstadoFactura::Pagada, $factura->fresh()->estado);
        $this->assertSame(EstadoPedido::Facturado, $pedido->fresh()->estado);
        $this->assertTrue($pedido->fresh()->factura->is($factura));
    }

    public function test_un_pedido_no_se_factura_dos_veces(): void
    {
        $pedido = $this->crearPedidoConProductos();
        $pedido->enviarACocina();
        $pedido->cambiarEstado(EstadoPedido::Listo);
        $pedido->cambiarEstado(EstadoPedido::Entregado);
        Factura::generar($pedido, 8);

        $this->expectException(LogicException::class);
        Factura::generar($pedido->fresh(), 8);
    }

    public function test_el_descuento_no_puede_superar_el_subtotal(): void
    {
        $pedido = $this->crearPedidoConProductos();
        $pedido->enviarACocina();
        $pedido->cambiarEstado(EstadoPedido::Listo);
        $pedido->cambiarEstado(EstadoPedido::Entregado);
        $factura = Factura::generar($pedido, 8);

        $this->expectException(ReglaNegocioException::class);
        $factura->aplicarDescuento(new DescuentoValorFijo((float) $factura->subtotal + 1));
    }

    public function test_producto_eliminado_conserva_el_historial(): void
    {
        $pedido   = $this->crearPedidoConProductos();
        $detalle  = $pedido->detalles()->first();
        $detalle->producto->delete();

        $this->assertSoftDeleted('productos', ['id' => $detalle->producto_id]);
        $this->assertNotNull($detalle->fresh()->producto);
    }

    public function test_no_se_puede_borrar_una_mesa_con_pedidos(): void
    {
        $this->crearPedidoConProductos();

        $this->expectException(QueryException::class);
        $this->mesa->delete();
    }

    public function test_usuario_eliminado_conserva_sus_pedidos(): void
    {
        $pedido = $this->crearPedidoConProductos();
        $this->mesero->delete();

        $this->assertSoftDeleted('users', ['id' => $this->mesero->id]);
        $this->assertNotNull($pedido->fresh()->usuario);
    }

    public function test_reserva_y_auditoria(): void
    {
        $this->actingAs($this->mesero);

        $reserva = Reserva::create([
            'mesa_id'        => $this->mesa->id,
            'user_id'        => $this->mesero->id,
            'nombre_cliente' => 'Cliente de prueba',
            'fecha'          => now()->addDay()->toDateString(),
            'hora'           => '19:30',
        ]);
        $reserva->cancelar();

        $registro = Auditoria::registrar('cancelar', $reserva, 'Reserva cancelada');

        $this->assertSame(EstadoReserva::Cancelada, $reserva->fresh()->estado);
        $this->assertSame('Reserva', $registro->entidad);
        $this->assertTrue($registro->usuario->is($this->mesero));
        $this->assertCount(1, $this->mesa->reservas);
    }
}
