@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0 text-gray-800"><i class="bi bi-pencil-square text-primary"></i> Modificar Producto: {{ $producto->nombre }}</h2>
                    <p class="text-muted small mb-0">Actualiza los precios, recetas, imagen o stock del plato.</p>
                </div>
                <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0 fs-6"><i class="bi bi-pencil"></i> Formulario de Actualización</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Nombre -->
                            <div class="col-md-7">
                                <label for="nombre" class="form-label fw-semibold">Nombre del Producto / Platillo <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre', $producto->nombre) }}" 
                                       required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-5">
                                <label for="categoria_id" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select @error('categoria_id') is-invalid @enderror" id="categoria_id" name="categoria_id" required>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
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
                                           value="{{ old('precio', $producto->precio) }}" 
                                           required>
                                    @error('precio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="col-md-4">
                                <label for="stock" class="form-label fw-semibold">Stock Actual <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="number" 
                                           class="form-control @error('stock') is-invalid @enderror" 
                                           id="stock" 
                                           name="stock" 
                                           value="{{ old('stock', $producto->stock) }}" 
                                           min="0" 
                                           required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-4">
                                <label for="estado" class="form-label fw-semibold">Disponibilidad</label>
                                <select class="form-select @error('estado') is-invalid @enderror" id="estado" name="estado">
                                    <option value="Disponible" {{ old('estado', $producto->estado) == 'Disponible' ? 'selected' : '' }}>🟢 Disponible</option>
                                    <option value="Agotado" {{ old('estado', $producto->estado) == 'Agotado' ? 'selected' : '' }}>🔴 Agotado</option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                          id="descripcion" 
                                          name="descripcion" 
                                          rows="3">{{ old('descripcion', $producto->descripcion) }}</textarea>
                                @error('descripcion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Imagen actual y nueva -->
                            <div class="col-12">
                                <label for="imagen" class="form-label fw-semibold">Imagen del Producto</label>
                                @if($producto->imagen)
                                    <div class="mb-2 d-flex align-items-center gap-3">
                                        <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="rounded shadow-sm" style="width: 70px; height: 70px; object-fit: cover;">
                                        <span class="text-muted small">Imagen actual. Si seleccionas un nuevo archivo, se reemplazará.</span>
                                    </div>
                                @endif
                                <input type="file" 
                                       class="form-control @error('imagen') is-invalid @enderror" 
                                       id="imagen" 
                                       name="imagen" 
                                       accept="image/*">
                                @error('imagen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
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
