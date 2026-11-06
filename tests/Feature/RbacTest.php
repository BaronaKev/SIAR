<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * RBAC: cada rol solo entra a lo que le corresponde, aunque escriba la URL a mano.
 */
class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function usuario(string $rol): User
    {
        return User::where('email', "{$rol}@siar.test")->firstOrFail();
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function accesos(): array
    {
        return [
            'admin ve usuarios'            => ['admin', 'usuarios.index', 200],
            'mesero NO ve usuarios'        => ['mesero', 'usuarios.index', 403],
            'cocinero NO ve usuarios'      => ['cocinero', 'usuarios.index', 403],
            'cajero NO ve usuarios'        => ['cajero', 'usuarios.index', 403],
            'mesero ve mesas'              => ['mesero', 'mesas.index', 200],
            'cocinero NO ve mesas'         => ['cocinero', 'mesas.index', 403],
            'mesero NO crea mesas'         => ['mesero', 'mesas.create', 403],
            'mesero crea pedidos'          => ['mesero', 'pedidos.create', 200],
            'cocinero NO crea pedidos'     => ['cocinero', 'pedidos.create', 403],
            'cajero NO crea pedidos'       => ['cajero', 'pedidos.create', 403],
            'cocinero ve cocina'           => ['cocinero', 'cocina.index', 200],
            'mesero NO ve cocina'          => ['mesero', 'cocina.index', 403],
            'cajero ve facturas'           => ['cajero', 'facturas.index', 200],
            'mesero NO ve facturas'        => ['mesero', 'facturas.index', 403],
            'mesero ve reservas'           => ['mesero', 'reservas.index', 200],
            'cajero NO ve reservas'        => ['cajero', 'reservas.index', 403],
            'admin ve auditoría'           => ['admin', 'auditorias.index', 200],
            'cajero NO ve auditoría'       => ['cajero', 'auditorias.index', 403],
            'cocinero ve productos'        => ['cocinero', 'productos.index', 200],
            'cocinero NO crea productos'   => ['cocinero', 'productos.create', 403],
        ];
    }

    #[DataProvider('accesos')]
    public function test_acceso_por_rol(string $rol, string $ruta, int $estado): void
    {
        $this->actingAs($this->usuario($rol))->get(route($ruta))->assertStatus($estado);
    }

    public function test_todos_los_roles_ven_el_dashboard_y_su_menu(): void
    {
        foreach (['admin', 'mesero', 'cocinero', 'cajero'] as $rol) {
            $this->actingAs($this->usuario($rol))->get(route('dashboard'))->assertOk();
        }
    }

    public function test_usuario_sin_rol_no_entra_a_ningun_modulo(): void
    {
        $sinRol = User::factory()->create();

        $this->actingAs($sinRol)->get(route('dashboard'))->assertOk()->assertSee('Sin rol asignado');
        $this->actingAs($sinRol)->get(route('mesas.index'))->assertForbidden();
        $this->actingAs($sinRol)->get(route('pedidos.index'))->assertForbidden();
    }

    public function test_usuario_registrado_no_puede_asignarse_el_rol_administrador(): void
    {
        $sinRol = User::factory()->create();

        $this->actingAs($sinRol)
            ->put(route('usuarios.update', $sinRol), [
                'name' => $sinRol->name, 'email' => $sinRol->email, 'role' => 'administrador', 'estado' => 1,
            ])
            ->assertForbidden();

        $this->assertFalse($sinRol->fresh()->hasRole('administrador'));
    }

    public function test_cocinero_no_puede_cancelar_ni_entregar_pedidos(): void
    {
        $pedido = Pedido::create([
            'mesa_id' => Mesa::first()->id,
            'user_id' => $this->usuario('mesero')->id,
        ]);

        $this->actingAs($this->usuario('cocinero'))
            ->patch(route('pedidos.estado', $pedido), ['estado' => 'Cancelado'])
            ->assertForbidden();
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $mesero = $this->usuario('mesero');
        $mesero->update(['estado' => false]);

        $this->post('/login', ['email' => $mesero->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Tu cuenta está inactiva. Contacta al administrador.']);

        $this->assertGuest();
    }

    public function test_usuario_desactivado_con_sesion_abierta_es_expulsado(): void
    {
        $mesero = $this->usuario('mesero');
        $this->actingAs($mesero);
        $mesero->update(['estado' => false]);

        $this->get(route('mesas.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_no_puede_desactivarse_ni_quitarse_el_rol(): void
    {
        $admin = $this->usuario('admin');

        $this->actingAs($admin)
            ->from(route('usuarios.edit', $admin))
            ->put(route('usuarios.update', $admin), [
                'name' => $admin->name, 'email' => $admin->email, 'role' => 'mesero', 'estado' => 1,
            ])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->hasRole('administrador'));
    }

    public function test_admin_crea_usuario_inactivo_con_rol(): void
    {
        $this->actingAs($this->usuario('admin'))
            ->post(route('usuarios.store'), [
                'name' => 'Nuevo Cajero', 'email' => 'nuevo@siar.test',
                'password' => 'secreto123', 'password_confirmation' => 'secreto123',
                'role' => 'cajero', 'estado' => 0,
            ])
            ->assertRedirect(route('usuarios.index'));

        $nuevo = User::where('email', 'nuevo@siar.test')->firstOrFail();
        $this->assertTrue($nuevo->hasRole('cajero'));
        $this->assertFalse($nuevo->estado);
        $this->assertSame('cajero', $nuevo->rol->name); // users.rol_id → roles.id
    }

    public function test_cambiar_el_rol_actualiza_rol_id(): void
    {
        $mesero = $this->usuario('mesero');
        $this->assertSame('mesero', $mesero->rol->name);

        $this->actingAs($this->usuario('admin'))
            ->put(route('usuarios.update', $mesero), [
                'name' => $mesero->name, 'email' => $mesero->email, 'role' => 'cajero', 'estado' => 1,
            ])
            ->assertRedirect(route('usuarios.index'));

        $mesero->refresh();
        $this->assertSame('cajero', $mesero->rol->name);
        $this->assertTrue($mesero->hasRole('cajero'));
        $this->assertFalse($mesero->hasRole('mesero'));
    }
}
