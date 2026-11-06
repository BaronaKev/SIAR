@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-plus-circle text-primary"></i> Registrar Nuevo Producto</h2>
                    <p class="text-muted small mb-0">Agrega un nuevo ítem al menú con su precio, categoría y stock inicial.</p>
                </div>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-card-checklist"></i> Información del Producto</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <!-- Nombre -->
                            <div class="col-md-7">
                                <label for="nombre" class="form-label fw-semibold">Nombre del Producto / Platillo <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre') }}" 
                                       placeholder="Ej: Hamburguesa Especial, Jugo de Maracuyá" 
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-5">
                                <label for="categoria_id" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select @error('categoria_id') is-invalid @enderror" id="categoria_id" name="categoria_id" required>
                                    <option value="">-- Seleccionar Categoría --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_id', request('categoria_id')) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Precio -->
                            <div class="col-md-4">
                                <label for="precio" class="form-label fw-semibold">Precio de Venta ($) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           step="0.01" 
                                           class="form-control @error('precio') is-invalid @enderror" 
                                           id="precio" 
                                           name="precio" 
                                           value="{{ old('precio') }}" 
                                           placeholder="0.00" 
                                           required>
                                    @error('precio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="col-md-4">
                                <label for="stock" class="form-label fw-semibold">Stock Inicial <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="number" 
                                           class="form-control @error('stock') is-invalid @enderror" 
                                           id="stock" 
                                           name="stock" 
                                           value="{{ old('stock', 10) }}" 
                                           min="0" 
                                           required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Si es 0, se marcará automáticamente 'Agotado'.</div>
                            </div>

                            <!-- Estado Inicial -->
                            <div class="col-md-4">
                                <label for="estado" class="form-label fw-semibold">Disponibilidad Inicial</label>
                                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                                    <option value="Disponible" {{ old('estado') == 'Disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                                    <option value="Agotado" {{ old('estado') == 'Agotado' ? 'selected' : '' }}>🔴 Agotado</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-semibold">Descripción o Ingredientes</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="3" 
                                          placeholder="Describe los ingredientes, presentación o acompañamientos...">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Imagen -->
                            <div class="col-12">
                                <label for="imagen" class="form-label fw-semibold">Fotografía del Producto (Opcional)</label>
                                <input type="file" 
                                       class="form-control @error('imagen') is-invalid @enderror" 
                                       id="imagen" 
                                       name="imagen" 
                                       accept="image/*">
                                @error('imagen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 2MB.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
