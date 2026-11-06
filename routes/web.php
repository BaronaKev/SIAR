<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CocinaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoEstadoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas web de SIAR
|--------------------------------------------------------------------------
| Todas las rutas internas exigen sesión iniciada ("auth"). La autorización
| por rol y permiso (RBAC) la aplica cada controlador con su Policy, de modo
| que una URL escrita a mano también queda protegida (responde 403).
*/

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // --- Perfil (Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Módulo 1: Usuarios ---
    Route::resource('usuarios', UsuarioController::class);

    // --- Módulo 2: Menú ---
    Route::resource('categorias', CategoriaController::class);
    Route::patch('productos/{producto}/cambiar-disponibilidad', [ProductoController::class, 'cambiarDisponibilidad'])
        ->name('productos.cambiarDisponibilidad');
    Route::resource('productos', ProductoController::class);

    // --- Módulo 3: Mesas y reservas ---
    Route::patch('mesas/{mesa}/cambiar-estado', [MesaController::class, 'cambiarEstado'])->name('mesas.cambiarEstado');
    Route::resource('mesas', MesaController::class);

    Route::patch('reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('reservas.cancelar');
    Route::patch('reservas/{reserva}/cumplir', [ReservaController::class, 'cumplir'])->name('reservas.cumplir');
    Route::resource('reservas', ReservaController::class)->except(['show', 'destroy']);

    // --- Módulo 4: Pedidos y cocina ---
    Route::get('cocina', CocinaController::class)->name('cocina.index');
    Route::resource('pedidos', PedidoController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('pedidos/{pedido}/estado', PedidoEstadoController::class)->name('pedidos.estado');
    Route::scopeBindings()->group(function () {
        Route::post('pedidos/{pedido}/detalles', [DetallePedidoController::class, 'store'])->name('pedidos.detalles.store');
        Route::patch('pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'update'])->name('pedidos.detalles.update');
        Route::delete('pedidos/{pedido}/detalles/{detalle}', [DetallePedidoController::class, 'destroy'])->name('pedidos.detalles.destroy');
    });

    // --- Módulo 5: Facturación ---
    Route::get('facturas', [FacturaController::class, 'index'])->name('facturas.index');
    Route::post('pedidos/{pedido}/factura', [FacturaController::class, 'store'])->name('facturas.store');
    Route::get('facturas/{factura}', [FacturaController::class, 'show'])->name('facturas.show');
    Route::patch('facturas/{factura}/descuento', [FacturaController::class, 'aplicarDescuento'])->name('facturas.descuento');
    Route::get('facturas/{factura}/comprobante', [FacturaController::class, 'comprobante'])->name('facturas.comprobante');
    Route::post('facturas/{factura}/pagos', [PagoController::class, 'store'])->name('facturas.pagos.store');

    // --- Auditoría (RNF006) ---
    Route::get('auditoria', AuditoriaController::class)->name('auditorias.index');
});

require __DIR__.'/auth.php';
