@extends('layouts.app')

@section('content')
{{-- HU021: la vista se recarga sola para mostrar los pedidos nuevos --}}
<meta http-equiv="refresh" content="{{ $refresco }}">

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0"><i class="bi bi-fire text-danger"></i> Cocina</h2>
            <p class="text-muted small mb-0">Se actualiza cada {{ $refresco }} segundos.</p>
        </div>
        <a href="{{ route('cocina.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Actualizar</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <h5 class="mb-3">En preparación <span class="badge bg-warning text-dark">{{ $enPreparacion->count() }}</span></h5>
            <div class="row g-3">
                @forelse($enPreparacion as $pedido)
                <div class="col-md-6">
                    <div class="card shadow-sm border-warning h-100">
                        <div class="card-header d-flex justify-content-between">
                            <strong>Pedido #{{ $pedido->id }} · Mesa {{ $pedido->mesa->numero }}</strong>
                            <small class="text-muted">{{ $pedido->updated_at->diffForHumans() }}</small>
                        </div>
                        <ul class="list-group list-group-flush">
                            @foreach($pedido->detalles as $detalle)
                                <li class="list-group-item"><strong>{{ $detalle->cantidad }} ×</strong> {{ $detalle->producto->nombre }}</li>
                            @endforeach
                        </ul>
                        @if($pedido->observaciones)
                            <div class="card-body py-2 small text-danger"><i class="bi bi-exclamation-circle"></i> {{ $pedido->observaciones }}</div>
                        @endif
                        @can('cambiarEstado', [$pedido, App\Enums\EstadoPedido::Listo])
                        <div class="card-footer bg-white">
                            <form action="{{ route('pedidos.estado', $pedido) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="estado" value="{{ App\Enums\EstadoPedido::Listo->value }}">
                                <button class="btn btn-success w-100"><i class="bi bi-check2-circle"></i> Marcar como listo</button>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
                @empty
                <div class="col-12"><div class="alert alert-light border">No hay pedidos en preparación.</div></div>
                @endforelse
            </div>
        </div>

        <div class="col-lg-4">
            <h5 class="mb-3">Listos para entregar <span class="badge bg-info text-dark">{{ $listos->count() }}</span></h5>
            <ul class="list-group shadow-sm">
                @forelse($listos as $pedido)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Pedido #{{ $pedido->id }} · Mesa {{ $pedido->mesa->numero }}</span>
                        <small class="text-muted">{{ $pedido->updated_at->diffForHumans() }}</small>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Nada pendiente de entrega.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
