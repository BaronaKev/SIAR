@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 mb-0"><i class="bi bi-pencil text-primary"></i> Editar reserva</h2>
                    <p class="text-muted small mb-0">Mesa {{ $reserva->mesa->numero }}. Para cambiar de mesa, cancela esta reserva y crea otra.</p>
                </div>
                <a href="{{ route('reservas.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('reservas.update', $reserva) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="row g-3">
                            @include('reservas._campos', ['reserva' => $reserva])
                        </div>
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('reservas.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
