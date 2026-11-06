@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-pencil-square text-primary"></i> Modificar Mesa {{ $mesa->numero }}</h2>
                    <p class="text-muted small mb-0">Actualiza las características o el estado de la mesa seleccionada.</p>
                </div>
                <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-pencil"></i> Formulario de Edición</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('mesas.update', $mesa) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Número / Identificador -->
                            <div class="col-md-6">
                                <label for="numero" class="form-label fw-semibold">Número o Identificador <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('numero') is-invalid @enderror" 
                                       id="numero" 
                                       name="numero" 
                                       value="{{ old('numero', $mesa->numero) }}" 
                                       required>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Capacidad -->
                            <div class="col-md-6">
                                <label for="capacidad" class="form-label fw-semibold">Capacidad (Personas) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    <input type="number" 
                                           class="form-control @error('capacidad') is-invalid @enderror" 
                                           id="capacidad" 
                                           name="capacidad" 
                                           value="{{ old('capacidad', $mesa->capacidad) }}" 
                                           min="1" 
                                           max="50" 
                                           required>
                                    @error('capacidad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <div class="col-md-6">
                                <label for="ubicacion" class="form-label fw-semibold">Ubicación / Sector</label>
                                <input type="text" 
                                       class="form-control @error('ubicacion') is-invalid @enderror" 
                                       id="ubicacion" 
                                       name="ubicacion" 
                                       value="{{ old('ubicacion', $mesa->ubicacion) }}">
                                @error('ubicacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6">
                                <label for="estado" class="form-label fw-semibold">Estado de la Mesa <span class="text-danger">*</span></label>
                                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                    <option value="Disponible" {{ old('estado', $mesa->estado) == 'Disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                                    <option value="Ocupada" {{ old('estado', $mesa->estado) == 'Ocupada' ? 'selected' : '' }}>🔴 Ocupada</option>
                                    <option value="Reservada" {{ old('estado', $mesa->estado) == 'Reservada' ? 'selected' : '' }}>🟡 Reservada</option>
                                    <option value="Mantenimiento" {{ old('estado', $mesa->estado) == 'Mantenimiento' ? 'selected' : '' }}>⚪ Mantenimiento</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('mesas.index') }}" class="btn btn-secondary">
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
