@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-info-circle text-primary"></i> Detalle de la Mesa {{ $mesa->numero }}</h2>
                    <p class="text-muted small mb-0">Información completa y estado operativo.</p>
                </div>
                <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-grid-3x3"></i> Ficha Técnica</h5>
                    @php
                        $badgeClass = match($mesa->estado) {
                            'Disponible' => 'bg-success',
                            'Ocupada' => 'bg-danger',
                            'Reservada' => 'bg-warning text-dark',
                            'Mantenimiento' => 'bg-secondary',
                            default => 'bg-info'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6 px-3 py-1">{{ $mesa->estado }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">IDENTIFICADOR / NÚMERO</span>
                            <span class="fs-5 fw-bold text-dark">{{ $mesa->numero }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">CAPACIDAD MÁXIMA</span>
                            <span class="fs-5 fw-bold text-dark"><i class="bi bi-people-fill text-primary"></i> {{ $mesa->capacidad }} Personas</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">UBICACIÓN / SECTOR</span>
                            <span class="fs-6 text-dark">{{ $mesa->ubicacion ?? 'Salón Principal' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">REGISTRADA EL</span>
                            <span class="fs-6 text-dark">{{ $mesa->created_at ? $mesa->created_at->format('d/m/Y H:i A') : 'N/A' }}</span>
                        </div>
                    </div>

                    @can('cambiarEstado', $mesa)
                    <hr class="my-4">

                    <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge text-warning"></i> Acciones Rápidas de Estado</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @if($mesa->estado !== 'Disponible')
                        <form action="{{ route('mesas.cambiarEstado', $mesa) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="Disponible">
                            <button type="submit" class="btn btn-outline-success">
                                <i class="bi bi-check-circle"></i> Liberar Mesa (Disponible)
                            </button>
                        </form>
                        @endif

                        @if($mesa->estado !== 'Ocupada')
                        <form action="{{ route('mesas.cambiarEstado', $mesa) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="Ocupada">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-dash-circle"></i> Ocupar Mesa
                            </button>
                        </form>
                        @endif

                        @if($mesa->estado !== 'Reservada')
                        <form action="{{ route('mesas.cambiarEstado', $mesa) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="estado" value="Reservada">
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-clock"></i> Reservar Mesa
                            </button>
                        </form>
                        @endif
                    </div>
                    @endcan
                </div>
                <div class="card-footer bg-light d-flex justify-content-end gap-2">
                    @can('create', App\Models\Pedido::class)
                    @unless($mesa->estaEnMantenimiento())
                    <a href="{{ route('pedidos.create', ['mesa_id' => $mesa->id]) }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Tomar pedido
                    </a>
                    @endunless
                    @endcan
                    @can('editar mesas')
                    <a href="{{ route('mesas.edit', $mesa) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Modificar Mesa
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
