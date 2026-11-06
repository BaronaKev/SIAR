@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-tags text-primary"></i> Categorías del Menú</h2>
            <p class="text-muted small mb-0">Organiza los productos, bebidas y platillos que ofrece el restaurante.</p>
        </div>
        @can('crear productos')
        <a href="{{ route('categorias.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Categoría
        </a>
        @endcan
    </div>

    <!-- Buscador -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('categorias.index') }}" class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar categoría por nombre o descripción..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Buscar
                    </button>
                    @if(request('buscar'))
                    <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary" title="Limpiar filtro">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Categorías -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">Nombre de Categoría</th>
                            <th scope="col">Descripción</th>
                            <th scope="col" class="text-center">Platos / Productos</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categorias as $categoria)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <strong class="text-dark fs-6"><i class="bi bi-bookmark-fill text-warning me-1"></i> {{ $categoria->nombre }}</strong>
                            </td>
                            <td>
                                {{ $categoria->descripcion ? Str::limit($categoria->descripcion, 60) : 'Sin descripción' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark px-2 py-1">
                                    {{ $categoria->productos_count }} producto(s)
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('categorias.show', $categoria) }}" class="btn btn-outline-info" title="Ver Productos">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('editar productos')
                                    <a href="{{ route('categorias.edit', $categoria) }}" class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('eliminar productos')
                                    <form action="{{ route('categorias.destroy', $categoria) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Deseas eliminar la categoría {{ $categoria->nombre }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-tags fs-2 d-block mb-2"></i>
                                No hay categorías registradas en el menú.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categorias->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $categorias->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
