@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-plus-circle text-primary"></i> Registrar Nueva Mesa</h2>
                    <p class="text-muted small mb-0">Ingresa la información para dar de alta una mesa en el salón.</p>
                </div>
                <a href="{{ route('mesas.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-info-circle"></i> Datos de la Mesa</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('mesas.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- Número / Identificador -->
                            <div class="col-md-6">
                                <label for="numero" class="form-label fw-semibold">Número o Identificador <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('numero') is-invalid @enderror" 
                                       id="numero" 
                                       name="numero" 
                                       value="{{ old('numero') }}" 
                                       placeholder="Ej: Mesa 01, VIP-A, Terraza-3" 
                                       required>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Debe ser un identificador único en el establecimiento.</div>
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
                                           value="{{ old('capacidad', 4) }}" 
                                           min="1" 
                                           max="50" 
                                           required>
                                    @error('capacidad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Cantidad máxima de comensales.</div>
                            </div>

                            <!-- Ubicación -->
                            <div class="col-md-6">
                                <label for="ubicacion" class="form-label fw-semibold">Ubicación / Sector</label>
                                <input type="text" 
                                       class="form-control @error('ubicacion') is-invalid @enderror" 
                                       id="ubicacion" 
                                       name="ubicacion" 
                                       value="{{ old('ubicacion') }}" 
                                       placeholder="Ej: Salón Principal, Terraza, Segundo Piso">
                                @error('ubicacion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado Inicial -->
                            <div class="col-md-6">
                                <label for="estado" class="form-label fw-semibold">Estado Inicial <span class="text-danger">*</span></label>
                                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                    <option value="Disponible" {{ old('estado') == 'Disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                                    <option value="Ocupada" {{ old('estado') == 'Ocupada' ? 'selected' : '' }}>🔴 Ocupada</option>
                                    <option value="Reservada" {{ old('estado') == 'Reservada' ? 'selected' : '' }}>🟡 Reservada</option>
                                    <option value="Mantenimiento" {{ old('estado') == 'Mantenimiento' ? 'selected' : '' }}>⚪ Mantenimiento</option>
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
                                <i class="bi bi-check-circle"></i> Guardar Mesa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
