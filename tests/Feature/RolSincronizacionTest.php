<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * users.rol_id es la fuente de verdad del rol y se refleja en Spatie (permisos).
 */
class RolSincronizacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_el_login_tiene_el_enlace_para_registrarse(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('register'), false)
            ->assertSee('Registrarse');

        $this->get(route('register'))->assertOk()->assertSee('Crear cuenta');
    }

    public function test_un_usuario_registrado_queda_sin_rol_hasta_que_se_lo_asignen(): void
    {
        $this->post(route('register'), [
            'name' => 'Nuevo', 'email' => 'nuevo@gmail.com',
            'password' => 'clave12345', 'password_confirmation' => 'clave12345',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->get(route('dashboard'))->assertOk()->assertSee('Sin rol asignado');
        $this->get(route('mesas.index'))->assertForbidden();
    }

    public function test_rol_id_editado_en_phpmyadmin_da_el_rol_y_sus_permisos(): void
    {
        $usuario = User::factory()->create();
        $admin   = Role::findByName('administrador', 'web');

        // Simula el cambio hecho a mano en phpMyAdmin (sin pasar por Eloquent).
        DB::table('users')->where('id', $usuario->id)->update(['rol_id' => $admin->id]);

        $this->actingAs($usuario->fresh())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Administrador')
            ->assertDontSee('Sin rol asignado');

        $this->assertTrue($usuario->fresh()->hasRole('administrador'));
        $this->actingAs($usuario->fresh())->get(route('usuarios.index'))->assertOk();
    }

    public function test_cambiar_rol_id_en_la_base_cambia_los_permisos(): void
    {
        $mesero = User::where('email', 'mesero@siar.test')->firstOrFail();
        DB::table('users')->where('id', $mesero->id)->update(['rol_id' => Role::findByName('cajero', 'web')->id]);

        $this->actingAs($mesero->fresh())->get(route('facturas.index'))->assertOk();
        $this->actingAs($mesero->fresh())->get(route('pedidos.create'))->assertForbidden();
        $this->assertSame(['cajero'], $mesero->fresh()->getRoleNames()->all());
    }

    public function test_rol_de_spatie_sin_rol_id_se_copia_a_rol_id(): void
    {
        $usuario = User::factory()->create();
        $usuario->assignRole('cocinero');
        $this->assertNull($usuario->fresh()->rol_id);

        $this->actingAs($usuario->fresh())->get(route('dashboard'))->assertOk()->assertSee('Cocinero');

        $this->assertSame('cocinero', $usuario->fresh()->rol->name);
    }

    public function test_comando_sincroniza_todos_los_usuarios(): void
    {
        $usuario = User::factory()->create();
        DB::table('users')->where('id', $usuario->id)->update(['rol_id' => Role::findByName('mesero', 'web')->id]);

        $this->artisan('siar:sincronizar-roles')
            ->expectsOutputToContain('Roles sincronizados')
            ->assertSuccessful();

        $this->assertTrue($usuario->fresh()->hasRole('mesero'));
    }

    public function test_la_cache_de_permisos_no_se_conserva_entre_peticiones(): void
    {
        // Así un permiso agregado en role_has_permissions desde phpMyAdmin aplica en la siguiente petición.
        $this->assertSame('array', config('permission.cache.store'));
    }
}
