@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-cup-hot text-primary"></i> Menú y Catálogo de Productos</h2>
            <p class="text-muted small mb-0">Gestiona los platillos, bebidas, precios, stock y disponibilidad para pedidos.</p>
        </div>
        @can('crear productos')
        <a href="{{ route('productos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Producto
        </a>
        @endcan
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('productos.index') }}" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o descripción..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="categoria_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todas las Categorías --</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todos los Estados --</option>
                        <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                        <option value="Agotado" {{ request('estado') == 'Agotado' ? 'selected' : '' }}>🔴 Agotado</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    @if(request('buscar') || request('categoria_id') || request('estado'))
                    <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">Foto</th>
                            <th scope="col">Producto</th>
                            <th scope="col">Categoría</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Disponibilidad</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $prod)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                @if($prod->imagen)
                                    <img src="{{ asset('storage/' . $prod->imagen) }}" alt="{{ $prod->nombre }}" class="rounded shadow-sm" style="width: 45px; height: 45px; object-fit: cover;">
                                @else
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted" style="width: 45px; height: 45px;">
                                        <i class="bi bi-card-image fs-5"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $prod->nombre }}</strong>
                                @if($prod->descripcion)
                                <div class="text-muted small">{{ Str::limit($prod->descripcion, 55) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-tag text-primary"></i> {{ $prod->categoria->nombre ?? 'Sin categoría' }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-success fs-6">${{ number_format($prod->precio, 2) }}</span>
                            </td>
                            <td>
                                @if($prod->stock <= 0)
                                    <span class="badge bg-danger">Agotado (0)</span>
                                @elseif($prod->stock <= 5)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> {{ $prod->stock }} u.</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success">{{ $prod->stock }} u.</span>
                                @endif
                            </td>
                            <td>
                                <!-- RF011: Acción rápida de cambio de disponibilidad (solo con permiso) -->
                                @can('cambiarDisponibilidad', $prod)
                                <form action="{{ route('productos.cambiarDisponibilidad', $prod) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $prod->estado === 'Disponible' ? 'btn-outline-success' : 'btn-outline-danger' }}" title="Haz clic para alternar disponibilidad (RF011)">
                                        <i class="bi {{ $prod->estado === 'Disponible' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i> {{ $prod->estado }}
                                    </button>
                                </form>
                                @else
                                <span class="badge {{ $prod->estado === 'Disponible' ? 'bg-success' : 'bg-danger' }}">{{ $prod->estado }}</span>
                                @endcan
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('productos.show', $prod) }}" class="btn btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('editar productos')
                                    <a href="{{ route('productos.edit', $prod) }}" class="btn btn-outline-primary" title="Editar Producto">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('eliminar productos')
                                    <form action="{{ route('productos.destroy', $prod) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas retirar {{ $prod->nombre }} del menú?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar Producto">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-cup-straw fs-2 d-block mb-2"></i>
                                No se encontraron productos en el menú.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($productos->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
