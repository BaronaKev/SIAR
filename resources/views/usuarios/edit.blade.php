@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-pencil-square text-primary"></i> Modificar Usuario: {{ $usuario->name }}</h2>
                    <p class="text-muted small mb-0">Actualiza los datos personales, el rol o renueva la contraseña.</p>
                </div>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-person-gear"></i> Editar Información de Usuario</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Nombre -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $usuario->name) }}" 
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
                                       value="{{ old('email', $usuario->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Rol RBAC -->
                            <div class="col-md-12">
                                <label for="role" class="form-label fw-semibold">Rol del Sistema <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role', $userRole) == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado de la cuenta (solo usuarios activos pueden iniciar sesión) -->
                            <div class="col-md-12">
                                <input type="hidden" name="estado" value="0">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="estado" name="estado" value="1" {{ old('estado', $usuario->estado) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="estado">Cuenta activa</label>
                                </div>
                                <div class="form-text">Un usuario inactivo no puede iniciar sesión, pero conserva su historial.</div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="alert alert-info py-2 small mb-0">
                                    <i class="bi bi-info-circle"></i> Deja los campos de contraseña en blanco si no deseas cambiarla.
                                </div>
                            </div>

                            <!-- Nueva Contraseña (Opcional) -->
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Nueva Contraseña (Opcional)</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Mínimo 8 caracteres">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Confirmación -->
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirmar Nueva Contraseña</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Repite la nueva contraseña">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
