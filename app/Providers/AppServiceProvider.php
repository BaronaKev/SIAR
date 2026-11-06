<?php

namespace App\Providers;

use App\Models\Categoria;
use App\Models\DetallePedido;
use App\Models\Factura;
use App\Models\Mesa;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\User;
use App\Observers\AuditoriaObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Modelos cuyas operaciones quedan en la auditoría (RNF006).
     * Para auditar otro modelo basta con agregarlo aquí (principio Abierto/Cerrado).
     *
     * @var list<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private const MODELOS_AUDITADOS = [
        User::class,
        Mesa::class,
        Categoria::class,
        Producto::class,
        Pedido::class,
        DetallePedido::class,
        Factura::class,
        Pago::class,
        Reserva::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Las vistas usan Bootstrap 5: la paginación debe generar sus clases.
        Paginator::useBootstrapFive();

        foreach (self::MODELOS_AUDITADOS as $modelo) {
            $modelo::observe(AuditoriaObserver::class);
        }
    }
}
