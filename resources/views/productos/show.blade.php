@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-info-circle text-primary"></i> Detalle de Producto</h2>
                    <p class="text-muted small mb-0">Información comercial, existencias y categoría en el menú.</p>
                </div>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al menú
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-cup-hot"></i> {{ $producto->nombre }}</h5>
                    <span class="badge {{ $producto->estado === 'Disponible' ? 'bg-success' : 'bg-danger' }} fs-6 px-3 py-1">
                        {{ $producto->estado }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-4 text-center">
                            @if($producto->imagen)
                                <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="img-fluid rounded shadow-sm" style="max-height: 220px; object-fit: cover;">
                            @else
                                <div class="bg-light border rounded d-flex flex-column align-items-center justify-content-center p-4 text-muted" style="height: 180px;">
                                    <i class="bi bi-card-image fs-1 mb-2"></i>
                                    <span>Sin imagen</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <span class="text-muted d-block small">CATEGORÍA</span>
                                    <span class="fs-6 fw-bold text-dark"><i class="bi bi-tag text-primary"></i> {{ $producto->categoria->nombre ?? 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block small">PRECIO DE VENTA</span>
                                    <span class="fs-4 fw-bold text-success">${{ number_format($producto->precio, 2) }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block small">INVENTARIO / STOCK</span>
                                    <span class="fs-5 fw-bold {{ $producto->stock <= 0 ? 'text-danger' : ($producto->stock <= 5 ? 'text-warning' : 'text-dark') }}">
                                        {{ $producto->stock }} unidades
                                    </span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block small">REGISTRADO EL</span>
                                    <span class="fs-6 text-dark">{{ $producto->created_at ? $producto->created_at->format('d/m/Y H:i A') : 'N/A' }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block small">DESCRIPCIÓN / INGREDIENTES</span>
                                    <p class="mb-0 text-secondary">{{ $producto->descripcion ?? 'Sin descripción detallada.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge text-warning"></i> Control de Cocina y Disponibilidad (RF011)</h6>
                    <form action="{{ route('productos.cambiarDisponibilidad', $producto) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @if($producto->estado === 'Disponible')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle"></i> Marcar como Agotado
                            </button>
                        @else
                            <button type="submit" class="btn btn-outline-success" {{ $producto->stock <= 0 ? 'disabled' : '' }}>
                                <i class="bi bi-check-circle"></i> Marcar como Disponible
                            </button>
                            @if($producto->stock <= 0)
                                <small class="text-danger ms-2">Requiere aumentar stock para habilitar disponibilidad.</small>
                            @endif
                        @endif
                    </form>
                </div>
                <div class="card-footer bg-light d-flex justify-content-end gap-2">
                    @can('editar productos')
                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Modificar Producto
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
