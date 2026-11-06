@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-grid-3x3 text-primary"></i> Gestión de Mesas</h2>
            <p class="text-muted small mb-0">Administra las mesas del establecimiento, su capacidad y estado en tiempo real.</p>
        </div>
        @can('crear mesas')
        <a href="{{ route('mesas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Mesa
        </a>
        @endcan
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('mesas.index') }}" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por número o ubicación..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="estado" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todos los Estados --</option>
                        <option value="Disponible" {{ request('estado') == 'Disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                        <option value="Ocupada" {{ request('estado') == 'Ocupada' ? 'selected' : '' }}>🔴 Ocupada</option>
                        <option value="Reservada" {{ request('estado') == 'Reservada' ? 'selected' : '' }}>🟡 Reservada</option>
                        <option value="Mantenimiento" {{ request('estado') == 'Mantenimiento' ? 'selected' : '' }}>⚪ Mantenimiento</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    @if(request('buscar') || request('estado'))
                    <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Mesas -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">Identificador / Número</th>
                            <th scope="col">Capacidad</th>
                            <th scope="col">Ubicación</th>
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mesas as $mesa)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $mesa->numero }}</strong>
                            </td>
                            <td>
                                <i class="bi bi-people-fill text-secondary"></i> {{ $mesa->capacidad }} personas
                            </td>
                            <td>
                                {{ $mesa->ubicacion ?? 'Principal' }}
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($mesa->estado) {
                                        'Disponible' => 'bg-success',
                                        'Ocupada' => 'bg-danger',
                                        'Reservada' => 'bg-warning text-dark',
                                        'Mantenimiento' => 'bg-secondary',
                                        default => 'bg-info'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    {{ $mesa->estado }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Botón Ver Detalle -->
                                    <a href="{{ route('mesas.show', $mesa) }}" class="btn btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('cambiarEstado', $mesa)
                                    <!-- Dropdown Cambiar Estado Rápido -->
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Cambiar Estado">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><h6 class="dropdown-header">Cambiar Estado</h6></li>
                                        <li>
                                            <form action="{{ route('mesas.cambiarEstado', $mesa) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="estado" value="Disponible">
                                                <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle"></i> Disponible</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('mesas.cambiarEstado', $mesa) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="estado" value="Ocupada">
                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-dash-circle"></i> Ocupada</button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('mesas.cambiarEstado', $mesa) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="estado" value="Reservada">
                                                <button type="submit" class="dropdown-item text-warning"><i class="bi bi-clock"></i> Reservada</button>
                                            </form>
                                        </li>
                                    </ul>
                                    @endcan

                                    @can('editar mesas')
                                    <a href="{{ route('mesas.edit', $mesa) }}" class="btn btn-outline-primary" title="Editar Mesa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('eliminar mesas')
                                    <form action="{{ route('mesas.destroy', $mesa) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar la mesa {{ $mesa->numero }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar Mesa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No se encontraron mesas registradas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($mesas->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $mesas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
