<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    @php($pesos = fn ($valor) => '$' . number_format((float) $valor, 0, ',', '.'))

    <div class="row g-4 mt-2">
        <div class="col-12">
            <h4><i class="bi bi-speedometer2"></i> Bienvenido, {{ Auth::user()->name }}</h4>
            <p class="text-muted">Rol actual:
                <span class="badge bg-dark">
                    {{ Auth::user()->rol ? ucfirst(Auth::user()->rol->name) : 'Sin rol asignado' }}
                </span>
            </p>
            @if(! Auth::user()->rol)
                <div class="alert alert-info">
                    Tu cuenta todavía no tiene un rol. Pide al administrador que te asigne uno para ver los módulos.
                </div>
            @endif
        </div>

        @can('ver mesas')
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Mesas disponibles</h6>
                        <h2 class="mb-0">{{ $indicadores['mesasDisponibles'] }} <small class="fs-6">/ {{ $indicadores['mesasTotal'] }}</small></h2>
                    </div>
                    <i class="bi bi-grid-3x3 fs-1 opacity-50"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('mesas.index') }}" class="text-white text-decoration-none small">Ver mesas <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver productos')
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Productos agotados</h6>
                        <h2 class="mb-0">{{ $indicadores['productosAgotados'] }} <small class="fs-6">/ {{ $indicadores['productosTotal'] }}</small></h2>
                    </div>
                    <i class="bi bi-cup-hot fs-1 opacity-50"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('productos.index') }}" class="text-white text-decoration-none small">Ver menú <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver pedidos')
        <div class="col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Pedidos activos</h6>
                        <h2 class="mb-0">{{ $indicadores['pedidosActivos'] }} <small class="fs-6">({{ $indicadores['enCocina'] }} en cocina)</small></h2>
                    </div>
                    <i class="bi bi-receipt fs-1 opacity-50"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('pedidos.index') }}" class="text-white text-decoration-none small">Ver pedidos <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver facturas')
        <div class="col-md-3">
            <div class="card text-white bg-dark h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Ventas de hoy</h6>
                        <h2 class="mb-0">{{ $pesos($indicadores['ventasHoy']) }}</h2>
                    </div>
                    <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('facturas.index') }}" class="text-white text-decoration-none small">Ver facturas <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @endcan

        @can('ver reservas')
        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Reservas para hoy</h6>
                        <h2 class="mb-0">{{ $indicadores['reservasHoy'] }}</h2>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('reservas.index', ['fecha' => today()->toDateString()]) }}" class="text-white text-decoration-none small">Ver reservas <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
        @endcan
    </div>
</x-app-layout>
