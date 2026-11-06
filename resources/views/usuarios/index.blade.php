@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-people text-primary"></i> Gestión de Usuarios</h2>
            <p class="text-muted small mb-0">Administra el personal del restaurante y asigna sus roles del sistema (RBAC).</p>
        </div>
        @can('crear usuarios')
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </a>
        @endcan
    </div>

    <!-- Barra de Búsqueda y Filtro por Rol -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('usuarios.index') }}" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o correo..." value="{{ request('buscar') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="rol" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Todos los Roles --</option>
                        @foreach($roles as $rolItem)
                            <option value="{{ $rolItem->name }}" {{ request('rol') == $rolItem->name ? 'selected' : '' }}>
                                {{ ucfirst($rolItem->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    @if(request('buscar') || request('rol'))
                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Correo Electrónico</th>
                            <th scope="col">Rol Asignado</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha de Registro</th>
                            <th scope="col" class="text-end pe-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $user)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-weight: bold;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-light text-primary border">Tú</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $rolName = $user->roles->first()?->name;
                                    $badgeClass = match($rolName) {
                                        'administrador' => 'bg-danger',
                                        'cajero' => 'bg-info text-dark',
                                        'mesero' => 'bg-primary',
                                        'cocinero' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    {{ ucfirst($rolName ?? 'Sin Rol') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $user->estado ? 'bg-success' : 'bg-secondary' }}">{{ $user->estado ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('usuarios.show', $user) }}" class="btn btn-outline-info" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('editar usuarios')
                                    <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-outline-primary" title="Editar Usuario">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan

                                    @can('eliminar usuarios')
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('usuarios.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar el usuario {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar Usuario">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2"></i>
                                No se encontraron usuarios registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($usuarios->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
