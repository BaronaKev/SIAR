@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-person-circle text-primary"></i> Perfil de Usuario</h2>
                    <p class="text-muted small mb-0">Detalles de la cuenta, rol y permisos habilitados en el sistema.</p>
                </div>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-person-badge"></i> Información de Cuenta</h5>
                    @php
                        $rolName = $usuario->roles->first()?->name;
                        $badgeClass = match($rolName) {
                            'administrador' => 'bg-danger',
                            'cajero' => 'bg-info text-dark',
                            'mesero' => 'bg-primary',
                            'cocinero' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} fs-6 px-3 py-1">{{ ucfirst($rolName ?? 'Sin Rol') }}</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">NOMBRE COMPLETO</span>
                            <span class="fs-5 fw-bold text-dark">{{ $usuario->name }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">CORREO ELECTRÓNICO</span>
                            <span class="fs-5 fw-bold text-dark">{{ $usuario->email }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">FECHA DE REGISTRO</span>
                            <span class="fs-6 text-dark">{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i A') : 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block small">ÚLTIMA ACTUALIZACIÓN</span>
                            <span class="fs-6 text-dark">{{ $usuario->updated_at ? $usuario->updated_at->format('d/m/Y H:i A') : 'N/A' }}</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-success"></i> Permisos Asignados por su Rol</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($usuario->getAllPermissions() as $permiso)
                            <span class="badge bg-light text-dark border p-2">
                                <i class="bi bi-check2 text-success"></i> {{ $permiso->name }}
                            </span>
                        @empty
                            <p class="text-muted small">No tiene permisos específicos asignados.</p>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light d-flex justify-content-end gap-2">
                    @can('editar usuarios')
                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Modificar Usuario
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
