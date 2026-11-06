<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

class SiarModulosTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles y permisos reales del sistema (RBAC)
        $this->seed(RoleSeeder::class);

        // Crear usuario administrador autenticado
        $this->adminUser = User::create([
            'name'     => 'Administrador SIAR',
            'email'    => 'admin@siar.test',
            'password' => bcrypt('password123'),
        ]);
        $this->adminUser->assignRole('administrador');
    }

    /**
     * Test Módulo Mesas: Listar y Crear
     */
    public function test_puede_listar_y_crear_mesas(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('mesas.store'), [
                'numero'    => 'Mesa 101',
                'capacidad' => 4,
                'ubicacion' => 'Terraza',
                'estado'    => 'Disponible',
            ]);

        $response->assertRedirect(route('mesas.index'));
        $this->assertDatabaseHas('mesas', [
            'numero'    => 'Mesa 101',
            'capacidad' => 4,
        ]);
    }

    /**
     * Test Módulo Mesas: Cambiar Estado
     */
    public function test_puede_cambiar_estado_de_mesa(): void
    {
        $mesa = Mesa::create([
            'numero'    => 'Mesa 202',
            'capacidad' => 6,
            'estado'    => 'Disponible',
        ]);

        $this->actingAs($this->adminUser)
            ->patch(route('mesas.cambiarEstado', $mesa), [
                'estado' => 'Ocupada',
            ]);

        $this->assertDatabaseHas('mesas', [
            'id'     => $mesa->id,
            'estado' => 'Ocupada',
        ]);
    }

    /**
     * Test Módulo Usuarios: Crear con Rol RBAC
     */
    public function test_puede_crear_usuario_con_rol_rbac(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('usuarios.store'), [
                'name'                  => 'Mesero Juan',
                'email'                 => 'juan@siar.test',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'mesero',
            ]);

        $response->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'juan@siar.test']);

        $nuevoUsuario = User::where('email', 'juan@siar.test')->first();
        $this->assertTrue($nuevoUsuario->hasRole('mesero'));
    }

    /**
     * Test Módulo Usuarios: Prevenir autoeliminación
     */
    public function test_no_permite_autoeliminacion_de_usuario_en_sesion(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->delete(route('usuarios.destroy', $this->adminUser));

        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

    /**
     * Test Módulo Categorías: Crear y validar unicidad
     */
    public function test_puede_crear_categoria_y_valida_unicidad(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('categorias.store'), [
                'nombre'      => 'Bebidas Calientes',
                'descripcion' => 'Café, té y aromáticas',
            ]);

        $response->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', ['nombre' => 'Bebidas Calientes']);

        // Intentar crear duplicada debe fallar con error de validación
        $duplicada = $this->actingAs($this->adminUser)
            ->post(route('categorias.store'), [
                'nombre' => 'Bebidas Calientes',
            ]);
        $duplicada->assertSessionHasErrors('nombre');
    }

    /**
     * Test Módulo Categorías: Regla CU010 impide eliminar categoría con productos
     */
    public function test_impide_eliminar_categoria_con_productos_asociados(): void
    {
        $categoria = Categoria::create(['nombre' => 'Postres']);
        Producto::create([
            'categoria_id' => $categoria->id,
            'nombre'       => 'Cheesecake',
            'precio'       => 12000,
            'stock'        => 5,
            'estado'       => 'Disponible',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('categorias.destroy', $categoria));

        // Debe conservar la categoría en la BD
        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
    }

    /**
     * Test Módulo Productos: Auto-cálculo de estado Agotado cuando stock es cero (RF011)
     */
    public function test_producto_se_marca_agotado_si_stock_es_cero(): void
    {
        $categoria = Categoria::create(['nombre' => 'Comidas Rápidas']);

        $this->actingAs($this->adminUser)
            ->post(route('productos.store'), [
                'categoria_id' => $categoria->id,
                'nombre'       => 'Perro Caliente Especial',
                'precio'       => 15000,
                'stock'        => 0, // Stock Cero
                'estado'       => 'Disponible',
            ]);

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Perro Caliente Especial',
            'stock'  => 0,
            'estado' => 'Agotado', // Lógica de negocio auto-cálculo
        ]);
    }

    /**
     * Test Módulo Productos: Alternar disponibilidad manual (RF011)
     */
    public function test_puede_alternar_disponibilidad_de_producto(): void
    {
        $categoria = Categoria::create(['nombre' => 'Bebidas']);
        $producto = Producto::create([
            'categoria_id' => $categoria->id,
            'nombre'       => 'Limonada Natural',
            'precio'       => 6000,
            'stock'        => 10,
            'estado'       => 'Disponible',
        ]);

        // Marcar como agotado
        $this->actingAs($this->adminUser)
            ->patch(route('productos.cambiarDisponibilidad', $producto));

        $this->assertDatabaseHas('productos', [
            'id'     => $producto->id,
            'estado' => 'Agotado',
        ]);
    }
}
