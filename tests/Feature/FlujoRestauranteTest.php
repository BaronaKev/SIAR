<?php

namespace Tests\Feature;

use App\Enums\EstadoFactura;
use App\Enums\EstadoMesa;
use App\Enums\EstadoPedido;
use App\Enums\EstadoReserva;
use App\Models\Auditoria;
use App\Models\Factura;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Flujo completo pedido por HTTP, cada paso con el rol que le corresponde:
 * Mesero → Cocina → Mesero → Cajero (sustentación del segundo corte).
 */
class FlujoRestauranteTest extends TestCase
{
    use RefreshDatabase;

    private User $mesero;
    private User $cocinero;
    private User $cajero;
    private Mesa $mesa;

    protected function setUp(): void
    {
        parent::setUp();
        config(['siar.impuesto_porcentaje' => 8]);
        $this->seed(DatabaseSeeder::class);

        $this->mesero   = User::where('email', 'mesero@siar.test')->firstOrFail();
        $this->cocinero = User::where('email', 'cocinero@siar.test')->firstOrFail();
        $this->cajero   = User::where('email', 'cajero@siar.test')->firstOrFail();
        $this->mesa     = Mesa::where('numero', '1')->firstOrFail();
    }

    private function crearPedido(int $cantidad = 2): Pedido
    {
        $producto = Producto::where('nombre', 'Bandeja paisa')->firstOrFail();

        $this->actingAs($this->mesero)
            ->post(route('pedidos.store'), [
                'mesa_id'   => $this->mesa->id,
                'productos' => [$producto->id => $cantidad],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        return Pedido::latest('id')->firstOrFail();
    }

    private function cambiarEstado(User $usuario, Pedido $pedido, EstadoPedido $estado): void
    {
        $this->actingAs($usuario)
            ->patch(route('pedidos.estado', $pedido), ['estado' => $estado->value])
            ->assertSessionHas('success');
    }

    public function test_flujo_completo_cliente_pedido_cocina_factura(): void
    {
        $producto    = Producto::where('nombre', 'Bandeja paisa')->firstOrFail();
        $stockInicial = $producto->stock;

        // 1. Mesero crea el pedido: descuenta stock y ocupa la mesa.
        $pedido = $this->crearPedido(2);
        $this->assertSame(EstadoPedido::Pendiente, $pedido->estado);
        $this->assertEquals(64000, (float) $pedido->total);
        $this->assertSame($stockInicial - 2, $producto->fresh()->stock);
        $this->assertSame(EstadoMesa::Ocupada->value, $this->mesa->fresh()->estado);

        // 2. Mesero lo envía a cocina; 3. el cocinero lo ve y lo marca listo; 4. el mesero lo entrega.
        $this->cambiarEstado($this->mesero, $pedido, EstadoPedido::EnPreparacion);
        $this->actingAs($this->cocinero)->get(route('cocina.index'))->assertOk()->assertSee("Pedido #{$pedido->id}");
        $this->cambiarEstado($this->cocinero, $pedido, EstadoPedido::Listo);
        $this->cambiarEstado($this->mesero, $pedido, EstadoPedido::Entregado);

        // 5. Cajero genera la factura, aplica 10 % de descuento y cobra en efectivo.
        $this->actingAs($this->cajero)->post(route('facturas.store', $pedido))->assertRedirect();
        $factura = Factura::where('pedido_id', $pedido->id)->firstOrFail();

        $this->actingAs($this->cajero)
            ->patch(route('facturas.descuento', $factura), ['tipo' => 'porcentaje', 'valor' => 10])
            ->assertSessionHas('success');

        $factura->refresh();
        $this->assertEquals(6400, (float) $factura->descuento);
        $this->assertEquals(4608, (float) $factura->impuesto);   // 8 % de 57.600
        $this->assertEquals(62208, (float) $factura->total);

        $this->actingAs($this->cajero)->get(route('facturas.show', $factura))->assertOk();

        $this->actingAs($this->cajero)
            ->post(route('facturas.pagos.store', $factura), [
                'metodo' => 'Efectivo', 'monto' => 62208, 'monto_recibido' => 70000,
            ])
            ->assertSessionHas('success', fn (string $mensaje) => str_contains($mensaje, 'Vueltas: $7.792'));

        // 6. Factura pagada, pedido facturado, mesa libre y comprobante disponible.
        $this->assertSame(EstadoFactura::Pagada, $factura->fresh()->estado);
        $this->assertSame(EstadoPedido::Facturado, $pedido->fresh()->estado);
        $this->assertSame(EstadoMesa::Disponible->value, $this->mesa->fresh()->estado);
        $this->actingAs($this->cajero)->get(route('facturas.comprobante', $factura))->assertOk()->assertSee($factura->numero);

        // 7. Todo quedó en la auditoría con su usuario.
        $this->assertTrue(Auditoria::where('entidad', 'Pedido')->where('user_id', $this->mesero->id)->exists());
        $this->assertTrue(Auditoria::where('entidad', 'Pago')->where('user_id', $this->cajero->id)->exists());
    }

    public function test_cancelar_pedido_devuelve_stock_y_libera_mesa(): void
    {
        $producto     = Producto::where('nombre', 'Bandeja paisa')->firstOrFail();
        $stockInicial = $producto->stock;

        $pedido = $this->crearPedido(3);
        $this->cambiarEstado($this->mesero, $pedido, EstadoPedido::Cancelado);

        $this->assertSame(EstadoPedido::Cancelado, $pedido->fresh()->estado);
        $this->assertSame($stockInicial, $producto->fresh()->stock);
        $this->assertSame(EstadoMesa::Disponible->value, $this->mesa->fresh()->estado);
    }

    public function test_no_se_puede_pedir_mas_del_stock_disponible(): void
    {
        $producto = Producto::where('nombre', 'Bandeja paisa')->firstOrFail();

        $this->actingAs($this->mesero)
            ->from(route('pedidos.create'))
            ->post(route('pedidos.store'), [
                'mesa_id'   => $this->mesa->id,
                'productos' => [$producto->id => $producto->stock + 1],
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, Pedido::count());
    }

    public function test_producto_agotado_cuando_el_stock_llega_a_cero(): void
    {
        $producto = Producto::where('nombre', 'Bandeja paisa')->firstOrFail();
        $producto->update(['stock' => 2]);

        $this->crearPedido(2);

        $this->assertSame(Producto::AGOTADO, $producto->fresh()->estado);
    }

    public function test_modificar_y_quitar_productos_solo_en_pendiente(): void
    {
        $pedido  = $this->crearPedido(2);
        $detalle = $pedido->detalles()->first();

        $this->actingAs($this->mesero)
            ->patch(route('pedidos.detalles.update', [$pedido, $detalle]), ['cantidad' => 1])
            ->assertSessionHas('success');
        $this->assertEquals(32000, (float) $pedido->fresh()->total);

        $this->cambiarEstado($this->mesero, $pedido, EstadoPedido::EnPreparacion);

        $this->actingAs($this->mesero)
            ->delete(route('pedidos.detalles.destroy', [$pedido, $detalle]))
            ->assertForbidden();
    }

    public function test_reserva_marca_la_mesa_y_al_cancelar_la_libera(): void
    {
        $this->actingAs($this->mesero)
            ->post(route('reservas.store'), [
                'mesa_id' => $this->mesa->id, 'nombre_cliente' => 'Laura Gómez',
                'fecha' => now()->addDay()->toDateString(), 'hora' => '20:00', 'personas' => 3,
            ])
            ->assertRedirect(route('reservas.index'));

        $reserva = Reserva::firstOrFail();
        $this->assertSame(EstadoMesa::Reservada->value, $this->mesa->fresh()->estado);

        $this->actingAs($this->mesero)->get(route('reservas.index'))->assertOk()->assertSee('Laura Gómez');
        $this->actingAs($this->mesero)->get(route('reservas.edit', $reserva))->assertOk()->assertSee('20:00');

        $this->actingAs($this->mesero)->patch(route('reservas.cancelar', $reserva))->assertSessionHas('success');

        $this->assertSame(EstadoReserva::Cancelada, $reserva->fresh()->estado);
        $this->assertSame(EstadoMesa::Disponible->value, $this->mesa->fresh()->estado);
    }

    public function test_no_se_reserva_una_mesa_ocupada(): void
    {
        $this->mesa->update(['estado' => EstadoMesa::Ocupada->value]);

        $this->actingAs($this->mesero)
            ->from(route('reservas.create'))
            ->post(route('reservas.store'), [
                'mesa_id' => $this->mesa->id, 'nombre_cliente' => 'Pedro',
                'fecha' => now()->toDateString(), 'hora' => '19:00', 'personas' => 2,
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, Reserva::count());
    }

    public function test_no_se_elimina_una_mesa_con_pedidos(): void
    {
        $this->crearPedido();
        $admin = User::where('email', 'admin@siar.test')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('mesas.index'))
            ->delete(route('mesas.destroy', $this->mesa))
            ->assertSessionHas('error');

        $this->assertNotNull($this->mesa->fresh());
    }

    public function test_las_vistas_principales_cargan(): void
    {
        $pedido = $this->crearPedido();
        $admin  = User::where('email', 'admin@siar.test')->firstOrFail();

        foreach ([
            route('pedidos.index'), route('pedidos.show', $pedido), route('pedidos.create'),
            route('mesas.show', $this->mesa), route('reservas.create'), route('auditorias.index'),
            route('usuarios.index'), route('usuarios.create'), route('usuarios.edit', $admin),
            route('productos.index'), route('categorias.index'), route('dashboard'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_la_auditoria_no_guarda_contrasenas(): void
    {
        $admin = User::where('email', 'admin@siar.test')->firstOrFail();
        $admin->update(['password' => 'otraClave123']);

        $registro = Auditoria::where('entidad', 'User')->where('accion', 'editar')->latest('id')->firstOrFail();

        $this->assertStringContainsString('contraseña actualizada', $registro->descripcion);
        $this->assertStringNotContainsString('otraClave123', $registro->descripcion);
        $this->assertStringNotContainsString('$2y$', $registro->descripcion);
    }
}
