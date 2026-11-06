@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-plus-circle text-primary"></i> Registrar Nueva Categoría</h2>
                    <p class="text-muted small mb-0">Crea una nueva clasificación para organizar los productos del menú.</p>
                </div>
                <a href="{{ route('categorias.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-tag"></i> Datos de la Categoría</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('categorias.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre de la Categoría <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}" 
                                   placeholder="Ej: Entradas, Platos Fuertes, Bebidas, Postres" 
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-semibold">Descripción (Opcional)</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="3" 
                                      placeholder="Breve detalle sobre los platos que componen esta categoría...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('categorias.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Categoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
