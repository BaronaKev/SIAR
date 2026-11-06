@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-person-plus text-primary"></i> Registrar Nuevo Usuario</h2>
                    <p class="text-muted small mb-0">Crea una cuenta para un miembro del personal y asígnale su rol correspondiente.</p>
                </div>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-person-badge"></i> Datos de Acceso y Perfil</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('usuarios.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Nombre -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       placeholder="Ej: Carlos Gómez" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Correo Electrónico -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       placeholder="correo@ejemplo.com" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Rol RBAC -->
                            <div class="col-md-12">
                                <label for="role" class="form-label fw-semibold">Rol del Sistema <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="">-- Selecciona un rol --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Define los permisos y módulos accesibles para esta cuenta.</div>
                            </div>

                            <!-- Estado de la cuenta (solo usuarios activos pueden iniciar sesión) -->
                            <div class="col-md-12">
                                <input type="hidden" name="estado" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="estado" name="estado" value="1" {{ old('estado', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="estado">Cuenta activa</label>
                                </div>
                                <div class="form-text">Un usuario inactivo no puede iniciar sesión, pero conserva su historial.</div>
                            </div>

                            <!-- Contraseña -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mínimo 8 caracteres" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirmación Contraseña -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Repite la contraseña" 
                                       required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
