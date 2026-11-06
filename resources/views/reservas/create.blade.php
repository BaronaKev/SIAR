@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0"><i class="bi bi-calendar-plus text-primary"></i> Nueva reserva</h2>
                    <p class="text-muted small mb-0">Solo se pueden reservar mesas en estado Disponible (HU015).</p>
                </div>
                <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('reservas.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="mesa_id" class="form-label fw-semibold">Mesa <span class="text-danger">*</span></label>
                                <select name="mesa_id" id="mesa_id" class="form-select @error('mesa_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccione --</option>
                                    @foreach($mesas as $mesa)
                                        <option value="{{ $mesa->id }}" @selected((int) old('mesa_id') === $mesa->id)>
                                            Mesa {{ $mesa->numero }} · {{ $mesa->capacidad }} personas · {{ $mesa->ubicacion ?? 'Principal' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('mesa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($mesas->isEmpty())
                                    <div class="form-text text-danger">No hay mesas disponibles en este momento.</div>
                                @endif
                            </div>
                            @include('reservas._campos')
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('reservas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar reserva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
