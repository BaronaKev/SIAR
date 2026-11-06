@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-tag text-primary"></i> Categoría: {{ $categoria->nombre }}</h2>
            <p class="text-muted small mb-0">{{ $categoria->descripcion ?? 'Sin descripción adicional registrada.' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Categorías
            </a>
            @can('crear productos')
            <a href="{{ route('productos.create') }}?categoria_id={{ $categoria->id }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Agregar Producto a esta Categoría
            </a>
            @endcan
        </div>
    </div>

    <!-- Listado de Productos de la Categoría -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fs-6"><i class="bi bi-cup-hot"></i> Platos y Productos en "{{ $categoria->nombre }}"</h5>
            <span class="badge bg-light text-dark">{{ $categoria->productos->count() }} producto(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Disponibilidad</th>
                            <th class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoria->productos as $prod)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $prod->nombre }}</strong>
                                @if($prod->descripcion)
                                <div class="text-muted small">{{ Str::limit($prod->descripcion, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold text-success">${{ number_format($prod->precio, 2) }}</span>
                            </td>
                            <td>
                                @if($prod->stock <= 0)
                                    <span class="badge bg-danger">0 u.</span>
                                @elseif($prod->stock <= 5)
                                    <span class="badge bg-warning text-dark">{{ $prod->stock }} u. (Bajo)</span>
                                @else
                                    <span class="badge bg-light text-dark border">{{ $prod->stock }} u.</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $prod->estado === 'Disponible' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $prod->estado }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('productos.show', $prod) }}" class="btn btn-sm btn-outline-info" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('editar productos')
                                <a href="{{ route('productos.edit', $prod) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No hay productos registrados en esta categoría aún.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
