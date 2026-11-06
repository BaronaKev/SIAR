@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0"><i class="bi bi-plus-circle text-primary"></i> Nuevo pedido</h2>
            <p class="text-muted small mb-0">Solo se muestran productos disponibles con stock (HU011).</p>
        </div>
        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white"><i class="bi bi-grid-3x3"></i> Mesa</div>
                    <div class="card-body">
                        <label for="mesa_id" class="form-label fw-semibold">Mesa <span class="text-danger">*</span></label>
                        <select name="mesa_id" id="mesa_id" class="form-select @error('mesa_id') is-invalid @enderror" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($mesas as $mesa)
                                <option value="{{ $mesa->id }}" @selected((int) old('mesa_id', $mesaSeleccionada) === $mesa->id)>
                                    Mesa {{ $mesa->numero }} · {{ $mesa->capacidad }} personas · {{ $mesa->estado }}
                                </option>
                            @endforeach
                        </select>

                        <label for="observaciones" class="form-label fw-semibold mt-3">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" rows="3" class="form-control"
                                  placeholder="Ej.: sin cebolla, término medio">{{ old('observaciones') }}</textarea>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-circle"></i> Crear pedido</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @forelse($categorias as $categoria)
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold">{{ $categoria->nombre }}</div>
                    <ul class="list-group list-group-flush">
                        @foreach($categoria->productos as $producto)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $producto->nombre }}</div>
                                <small class="text-muted">${{ number_format((float) $producto->precio, 0, ',', '.') }} · stock {{ $producto->stock }}</small>
                            </div>
                            <input type="number" name="productos[{{ $producto->id }}]" min="0" max="{{ min(100, $producto->stock) }}"
                                   value="{{ old('productos.' . $producto->id, 0) }}" class="form-control" style="width: 90px"
                                   aria-label="Cantidad de {{ $producto->nombre }}">
                        </li>
                        @endforeach
                    </ul>
                </div>
                @empty
                <div class="alert alert-warning">No hay productos disponibles en el menú.</div>
                @endforelse
            </div>
        </div>
    </form>
</div>
@endsection
